<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability\APM\Agent\NewRelic;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use stdClass;
use ZERO2TEN\Observability\APM\Agent\BrowserAgentInterface;
use ZERO2TEN\Observability\APM\Agent\NewRelic\NewRelicAgent;
use ZERO2TEN\Observability\APM\Agent\TransactionAgentInterface;
use ZERO2TEN\Observability\APM\AgentInterface;
use ZERO2TEN\Observability\APM\Datastore;
use ZERO2TEN\Tests\Observability\GenericDataProviders;

/**
 * NewRelicAgentTest
 *
 * @coversDefaultClass \ZERO2TEN\Observability\APM\Agent\NewRelic\NewRelicAgent
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Tests\Observability\APM\Agent\NewRelic
 */
class NewRelicAgentTest extends TestCase
{
    use GenericDataProviders;

    private const APP_NAME = 'App name';
    private const LICENSE = 'LICENSE-X234';

    /** @var NewRelicAgent|MockObject */
    private $agentMock;

    /**
     * @throws ReflectionException
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var NewRelicAgent|MockObject $agentMock */
        $agentMock = $this->getMockBuilder(NewRelicAgent::class)
            ->onlyMethods([
                'isExtensionLoaded',
                'getConfigurationOption',
                '__call',
            ])
            ->getMock()
        ;

        $agentMock
            ->expects($this->any())
            ->method('isExtensionLoaded')
            ->with('newrelic')
            ->willReturn(true)
        ;

        $agentMock
            ->expects($this->any())
            ->method('getConfigurationOption')
            ->willReturnMap([
                ['newrelic.appname', self::APP_NAME],
                ['newrelic.license', self::LICENSE],
            ])
        ;

        $rc = new ReflectionClass($agentMock);
        $rc->getConstructor()->invoke($agentMock);

        $this->agentMock = $agentMock;
    }

    /**
     * @coversNothing
     * @group unit
     *
     * @return void
     */
    public function testAgent(): void
    {
        $agent = new NewRelicAgent();

        $this->assertInstanceOf(AgentInterface::class, $agent);
        $this->assertInstanceOf(BrowserAgentInterface::class, $agent);
        $this->assertInstanceOf(TransactionAgentInterface::class, $agent);
    }

    /**
     * @covers ::initialise
     * @group unit
     *
     * @dataProvider initialiseDataProvider
     *
     * @param bool $extensionLoaded
     * @param array $configurationOptions
     * @param bool $expectedResult
     * @throws ReflectionException
     * @return void
     */
    public function testInitialise(bool $extensionLoaded, array $configurationOptions, bool $expectedResult): void
    {
        /** @var NewRelicAgent|MockObject $agentMock */
        $agentMock = $this->getMockBuilder(NewRelicAgent::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isExtensionLoaded',
                'getConfigurationOption',
            ])
            ->getMock()
        ;

        $agentMock
            ->expects($this->once())
            ->method('isExtensionLoaded')
            ->with('newrelic')
            ->willReturn($extensionLoaded)
        ;

        $agentMock
            ->expects($extensionLoaded ? $this->exactly(2) : $this->never())
            ->method('getConfigurationOption')
            ->willReturnMap($configurationOptions)
        ;

        $rc = new ReflectionClass($agentMock);
        $rc->getConstructor()->invoke($agentMock);

        $this->assertSame($expectedResult, $agentMock->isSupported());
    }

    /**
     * @return array[]
     */
    public function initialiseDataProvider(): array
    {
        return [
            'extension not loaded' => [
                false,
                [],
                false,
            ],
            'extension loaded, no INI values' => [
                true,
                [],
                false,
            ],
            'extension loaded, INI values available' => [
                true,
                [
                    ['newrelic.appname', 'Fancy app'],
                    ['newrelic.license', '1234567890'],
                ],
                true,
            ],
            'extension loaded, no app name' => [
                true,
                [
                    ['newrelic.appname', ''],
                    ['newrelic.license', 'important'],
                ],
                false,
            ],
            'extension loaded, no license' => [
                true,
                [
                    ['newrelic.appname', 'Amazing'],
                    ['newrelic.license', ''],
                ],
                false,
            ],
        ];
    }

    /**
     * @covers ::changeApplicationName
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $ignoreTransaction
     * @return void
     */
    public function testChangeApplicationName(bool $ignoreTransaction): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_set_appname', [
                'some-app-name',
                self::LICENSE,
                !$ignoreTransaction,
            ])
        ;

        $this->agentMock->changeApplicationName('some-app-name', $ignoreTransaction);
    }

    /**
     * @covers ::captureUrlParameters
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $enable
     * @return void
     */
    public function testCaptureUrlParameters(bool $enable): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_capture_params', [$enable])
        ;

        $this->agentMock->captureUrlParameters($enable);
    }

    /**
     * @covers ::recordCustomEvent
     * @covers ::guardIsNotReservedWord
     * @group unit
     *
     * @return void
     */
    public function testRecordCustomEvent(): void
    {
        $expectedAttributes = [
            'a' => 'A',
            'b' => 2,
            'c' => true,
            'd' => 9.567,
        ];

        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_record_custom_event', [
                'SpecialEventName',
                $expectedAttributes,
            ])
        ;

        $this->agentMock->recordCustomEvent('SpecialEventName', $expectedAttributes);
    }

    /**
     * @covers ::recordCustomEvent
     * @covers ::guardIsNotReservedWord
     * @group unit
     *
     * @dataProvider reservedWordsDataProvider
     *
     * @param string $name
     * @return void
     */
    public function testRecordCustomEventWithReservedWord(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot use reserved word "' . $name . '" as metric name.');

        $this->agentMock->expects($this->never())->method('__call');

        $this->agentMock->recordCustomEvent($name, []);
    }

    /**
     * @covers ::addCustomMetric
     * @covers ::guardIsNotReservedWord
     * @group unit
     *
     * @throws Exception
     * @return void
     */
    public function testAddCustomMetric(): void
    {
        $random = random_int(678900, 99988877) / 1000;

        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_custom_metric', [
                'Custom/ImportantMetric',
                $random,
            ])
        ;

        $this->agentMock->addCustomMetric('ImportantMetric', $random);
    }

    /**
     * @covers ::addCustomMetric
     * @covers ::guardIsNotReservedWord
     * @group unit
     *
     * @dataProvider reservedWordsDataProvider
     *
     * @param string $name
     * @return void
     */
    public function testAddCustomMetricWithReservedWord(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot use reserved word "' . $name . '" as metric name.');

        $this->agentMock->expects($this->never())->method('__call');

        $this->agentMock->addCustomMetric($name, 0.0);
    }

    /**
     * @covers ::disableAutomaticBrowserMonitoringScripts
     * @group unit
     *
     * @return void
     */
    public function testDisableAutomaticBrowserMonitoringScripts(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_disable_autorum')
        ;

        $this->agentMock->disableAutomaticBrowserMonitoringScripts();
    }

    /**
     * @covers ::getBrowserMonitoringHeaderScript
     * @group unit
     *
     * @return void
     */
    public function testGetBrowserMonitoringHeaderScript(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_get_browser_timing_header', [
                false,
            ])
            ->willReturn('header-script')
        ;

        $this->assertSame('header-script', $this->agentMock->getBrowserMonitoringHeaderScript());
    }

    /**
     * @covers ::getBrowserMonitoringFooterScript
     * @group unit
     *
     * @return void
     */
    public function testGetBrowserMonitoringFooterScript(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_get_browser_timing_footer', [
                false,
            ])
            ->willReturn('footer-script')
        ;

        $this->assertSame('footer-script', $this->agentMock->getBrowserMonitoringFooterScript());
    }

    /**
     * @covers ::startTransaction
     * @group unit
     *
     * @dataProvider startTransactionDataProvider
     *
     * @param bool $endSucceeds
     * @param bool $startSucceeds
     * @param array[] $infoLogCalls
     * @return void
     */
    public function testStartTransaction(bool $endSucceeds, bool $startSucceeds, array $infoLogCalls): void
    {
        /** @var LoggerInterface|MockObject $loggerMock */
        $loggerMock = $this->createMock(LoggerInterface::class);

        $loggerMock
            ->expects($this->exactly(count($infoLogCalls)))
            ->method('info')
            ->withConsecutive(...$infoLogCalls)
        ;

        $this->agentMock->setLogger($loggerMock);

        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->willReturnMap([
                [
                    'newrelic_start_transaction',
                    [
                        self::APP_NAME,
                        self::LICENSE,
                    ],
                    $startSucceeds,
                ],
            ])
        ;

        $this->agentMock->startTransaction(false);
    }

    /**
     * @return array[]
     */
    public function startTransactionDataProvider(): array
    {
        return [
            'Success' => [
                true,
                true,
                [],
            ],
        ];
    }

    /**
     * @covers ::changeTransactionName
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $isChanged
     * @return void
     */
    public function testChangeTransactionName(bool $isChanged): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_name_transaction', [
                'new-transaction-name',
            ])
            ->willReturn($isChanged)
        ;

        $this->agentMock->changeTransactionName('new-transaction-name');
    }

    /**
     * @covers ::addTransactionParameter
     * @group unit
     *
     * @dataProvider transactionParameterDataProvider
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function testAddTransactionParameter(string $name, $value): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_add_custom_parameter', [
                $name,
                $value,
            ])
        ;

        $this->agentMock->addTransactionParameter($name, $value);
    }

    /**
     * @return array[]
     */
    public function transactionParameterDataProvider(): array
    {
        return [
            'null' => [
                'test',
                null,
            ],
            'integer' => [
                'domains',
                15,
            ],
            'float' => [
                'price',
                15.99,
            ],
            'boolean' => [
                'yes_or_no',
                false,
            ],
        ];
    }

    /**
     * @covers ::addTransactionParameter
     * @group unit
     *
     * @dataProvider nonScalarTransactionParameterDataProvider
     *
     * @param string $name
     * @param mixed $value
     * @param string $expectedMessage
     * @return void
     */
    public function testAddTransactionParameterWithNonScalarValue(string $name, $value, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->agentMock->expects($this->never())->method('__call');

        $this->agentMock->addTransactionParameter($name, $value);
    }

    /**
     * @return array[]
     */
    public function nonScalarTransactionParameterDataProvider(): array
    {
        return [
            'object' => [
                'test',
                new stdClass(),
                'Transaction parameter value must be scalar, "object" given.',
            ],
            'another object' => [
                'test',
                (object)[],
                'Transaction parameter value must be scalar, "object" given.',
            ],
            'array' => [
                'some-array',
                [
                    'test' => true,
                ],
                'Transaction parameter value must be scalar, "array" given.',
            ],
        ];
    }

    /**
     * @covers ::markTransactionAsBackground
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $isBackground
     * @return void
     */
    public function testMarkTransactionAsBackground(bool $isBackground): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_background_job', [
                $isBackground,
            ])
        ;

        $this->agentMock->markTransactionAsBackground($isBackground);
    }

    /**
     * @covers ::recordTransactionException
     * @group unit
     *
     * @return void
     */
    public function testRecordTransactionException(): void
    {
        $exception = new Exception('Another message', 0);

        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_notice_error', [
                'Some exception explanation message',
                $exception,
            ])
        ;

        $this->agentMock->recordTransactionException('Some exception explanation message', $exception);
    }

    /**
     * @covers ::addTransactionDatastoreSegment
     * @group unit
     *
     * @dataProvider variousResultsDataProvider
     *
     * @param mixed $expectedResult
     * @return void
     */
    public function testAddTransactionDatastoreSegment($expectedResult): void
    {
        /** @var Datastore|MockObject $datastoreMock */
        $datastoreMock = $this->createMock(Datastore::class);
        $datastoreMock->expects($this->once())->method('product')->willReturn('the-product');
        $datastoreMock->expects($this->once())->method('collection')->willReturn('the-collection');
        $datastoreMock->expects($this->once())->method('operation')->willReturn('the-operation');
        $datastoreMock->expects($this->once())->method('host')->willReturn('the-host');
        $datastoreMock->expects($this->once())->method('database')->willReturn('the-database');

        $callback = static function () use ($expectedResult) {
            return $expectedResult;
        };

        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_record_datastore_segment', [
                $callback,
                [
                    'product' => 'the-product',
                    'collection' => 'the-collection',
                    'operation' => 'the-operation',
                    'host' => 'the-host',
                    'databaseName' => 'the-database',
                    'query' => 'some-query',
                    'inputQueryLabel' => 'important label',
                    'inputQuery' => 'actual query',
                ],
            ])
            ->willReturn($expectedResult)
        ;

        $result = $this->agentMock->addTransactionDatastoreSegment(
            $datastoreMock,
            $callback,
            'some-query',
            'important label',
            'actual query'
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @covers ::stopTransactionTiming
     * @group unit
     *
     * @return void
     */
    public function testStopTransactionTiming(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_end_of_transaction')
        ;

        $this->agentMock->stopTransactionTiming();
    }

    /**
     * @covers ::ignoreTransactionApdex
     * @group unit
     *
     * @return void
     */
    public function testIgnoreTransactionApdex(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_ignore_apdex')
        ;

        $this->agentMock->ignoreTransactionApdex();
    }

    /**
     * @covers ::ignoreTransaction
     * @group unit
     *
     * @return void
     */
    public function testIgnoreTransaction(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_ignore_transaction')
        ;

        $this->agentMock->ignoreTransaction();
    }

    /**
     * @covers ::isTransactionSampled
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $isSampled
     * @return void
     */
    public function testIsTransactionSampled(bool $isSampled): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_is_sampled')
            ->willReturn($isSampled)
        ;

        $this->assertSame($isSampled, $this->agentMock->isTransactionSampled());
    }

    /**
     * @covers ::endTransaction
     * @group unit
     *
     * Note that `endTransaction()` is called twice. This is not a typo, it is
     * to validate that the internal function is only called once.
     *
     * @return void
     */
    public function testEndTransaction(): void
    {
        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_end_transaction', [
                false,
            ])
            ->willReturn(true)
        ;

        $this->agentMock->endTransaction();
        $this->agentMock->endTransaction();
    }


    /**
     * @covers ::isTransactionEnded
     * @group unit
     *
     * @return void
     */
    public function testIsTransactionEnded(): void
    {
        $this->assertFalse($this->agentMock->isTransactionEnded());

        $this->agentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_end_transaction', [
                false,
            ])
            ->willReturn(true)
        ;

        $this->agentMock->endTransaction();

        $this->assertTrue($this->agentMock->isTransactionEnded());
    }

    /**
     * @return array[]
     */
    public function reservedWordsDataProvider(): array
    {
        return [
            ['accountId'],
            ['appId'],
            ['timestamp'],
            ['type'],
            ['eventType'],
        ];
    }
}
