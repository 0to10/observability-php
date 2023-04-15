<?php
declare(strict_types=1);
namespace Nouve\Tests\APM\Agents;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Nouve\APM\Agents\NewRelicAgent;
use Nouve\APM\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use stdClass;
use ZERO2TEN\Observability\APM\Agent\NewRelic\NewRelicAgent as NewRelicAgentV2;

use function extension_loaded;

/**
 * NewRelicAgentTest
 *
 * @coversDefaultClass \Nouve\APM\Agents\NewRelicAgent
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\Tests\APM\Agents
 */
class NewRelicAgentTest extends TestCase
{
    /** @var NewRelicAgentV2|MockObject */
    private $newAgentMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->newAgentMock = $this->getMockBuilder(NewRelicAgentV2::class)
            ->onlyMethods([
                'isExtensionLoaded',
                'getConfigurationOption',
                '__call',
            ])
            ->getMock()
        ;
    }

    /**
     * @group unit
     * @covers ::__construct
     *
     * @throws ReflectionException
     * @return void
     */
    public function testConstruct(): void
    {
        /** @var NewRelicAgent|MockObject $agentMock */
        $agentMock = $this->getMockBuilder(NewRelicAgent::class)
            ->onlyMethods([
                'getConfigurationOption',
            ])
            ->getMock()
        ;

        $agentMock
            ->expects($this->exactly(2))
            ->method('getConfigurationOption')
            ->willReturnMap([
                ['newrelic.appname', 'Application name'],
                ['newrelic.license', '0123456789ABC'],
            ])
        ;

        $rc = new ReflectionClass($agentMock);
        $rc->getConstructor()->invoke($agentMock);
    }

    /**
     * @group unit
     * @covers ::__construct
     *
     * @throws ReflectionException
     * @return void
     */
    public function testConstructWithCustomLicense(): void
    {
        /** @var NewRelicAgent|MockObject $agentMock */
        $agentMock = $this->getMockBuilder(NewRelicAgent::class)
            ->onlyMethods([
                'getConfigurationOption',
            ])
            ->getMock()
        ;

        $agentMock
            ->expects($this->once())
            ->method('getConfigurationOption')
            ->willReturnMap([
                ['newrelic.license', '0123456789ABC'],
            ])
        ;

        $rc = new ReflectionClass($agentMock);
        $rc->getConstructor()->invoke($agentMock, 'SomeLicense');
    }

    /**
     * @group unit
     * @covers ::stopTransactionTiming
     *
     * @return void
     */
    public function testStopTransactionTiming(): void
    {
        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_end_of_transaction')
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);
        $agent->stopTransactionTiming();
    }

    /**
     * @group unit
     * @covers ::endTransaction
     *
     * @return void
     */
    public function testEndTransaction(): void
    {
        $this->newAgentMock
            ->expects($this->any())
            ->method('__call')
            ->with('newrelic_end_transaction', [])
            ->willReturnOnConsecutiveCalls(
                true,
                false,
            )
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);

        $this->assertTrue($agent->endTransaction());
        $this->assertFalse($agent->endTransaction());
    }

    /**
     * @group unit
     * @covers ::setApplicationName
     *
     * @return void
     */
    public function testSetApplicationName(): void
    {
        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_set_appname', [
                'SomeName',
                'license-code',
            ])
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent('license-code', $this->newAgentMock);

        $this->assertTrue($agent->setApplicationName('SomeName'));
    }

    /**
     * @group unit
     * @covers ::recordEvent
     * @covers ::guardIsNotReservedWord
     *
     * @return void
     */
    public function testRecordEvent(): void
    {
        $eventName = 'MyEvent';
        $eventAttributes = [
            'attribute1' => 'test',
            'attribute2' => 50,
        ];

        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_record_custom_event', [
                $eventName,
                $eventAttributes,
            ])
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);
        $agent->recordEvent($eventName, $eventAttributes);
    }

    /**
     * @group unit
     * @covers ::recordEvent
     * @covers ::guardIsNotReservedWord
     *
     * @dataProvider reservedWordsProvider
     *
     * @param string $reservedWord
     * @return void
     */
    public function testRecordEventWithReservedWordThrowsException(string $reservedWord): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Cannot use reserved word "\w+" as metric name\./');

        $this->newAgentMock
            ->expects($this->never())
            ->method('__call')
            ->with('newrelic_record_custom_event')
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);
        $agent->recordEvent($reservedWord, []);
    }

    /**
     * @return array[]
     */
    public function reservedWordsProvider(): array
    {
        return [
            ['appId'],
            ['timestamp'],
        ];
    }

    /**
     * @group unit
     * @covers ::setTransactionName
     *
     * @return void
     */
    public function testSetTransactionName(): void
    {
        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_name_transaction', [
                'MyTransaction',
            ])
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);

        $this->assertTrue($agent->setTransactionName('MyTransaction'));
    }

    /**
     * @group unit
     * @covers ::ignoreTransaction
     *
     * @return void
     */
    public function testIgnoreTransaction(): void
    {
        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_ignore_transaction')
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);
        $agent->ignoreTransaction();
    }

    /**
     * @group unit
     * @covers ::flagTransaction
     *
     * @return void
     */
    public function testFlagTransaction(): void
    {
        $this->newAgentMock
            ->expects($this->exactly(2))
            ->method('__call')
            ->with('newrelic_background_job')
            ->willReturnMap([
                [true],
                [false],
            ])
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);

        $agent->flagTransaction(true);
        $agent->flagTransaction(false);
    }

    /**
     * @group unit
     * @covers ::recordException
     *
     * @return void
     */
    public function testRecordException(): void
    {
        $exception = new Exception('Some exception message.');

        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_notice_error', [
                $exception->getMessage(),
                $exception,
            ])
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);
        $agent->recordException($exception);
    }

    /**
     * @group unit
     * @covers ::startTransaction
     *
     * @return void
     */
    public function testStartTransaction(): void
    {
        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_start_transaction')
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);

        $this->assertInstanceOf(Transaction::class, $agent->startTransaction());
    }

    /**
     * @group unit
     * @covers ::startTransaction
     *
     * @return void
     */
    public function testStartTransactionRetries(): void
    {
        $this->newAgentMock
            ->expects($this->exactly(3))
            ->method('__call')
            ->willReturnMap([
                ['newrelic_start_transaction', false],
                ['newrelic_end_transaction', true],
                ['newrelic_start_transaction', true],
            ])
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);

        $this->assertInstanceOf(Transaction::class, $agent->startTransaction());
    }

    /**
     * @group unit
     * @covers ::addTransactionParameter
     * @covers ::guardIsNotReservedWord
     *
     * @return void
     */
    public function testAddTransactionParameter(): void
    {
        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_add_custom_parameter',[
                'SomeParameter',
                'test',
            ])
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);
        $agent->addTransactionParameter('SomeParameter', 'test');
    }

    /**
     * @group unit
     * @covers ::addTransactionParameter
     *
     * @return void
     */
    public function testAddTransactionParameterWithNonScalarThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Transaction parameter value must be scalar, "\w+" given\./');

        $value = new stdClass();

        $this->newAgentMock
            ->expects($this->never())
            ->method('__call')
            ->with('newrelic_add_custom_parameter', [
                'SomeParameter',
                $value,
            ])
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);
        $agent->addTransactionParameter('SomeParameter', $value);
    }

    /**
     * @return void
     */
    public function testIgnoreTransactionApdex(): void
    {

        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_ignore_apdex')
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);
        $agent->ignoreTransactionApdex();
    }

    /**
     * @group unit
     * @covers ::addTransactionMetric
     *
     * @return void
     */
    public function testAddTransactionMetric(): void
    {
        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_custom_metric', [
                'Custom/SomeMetric',
                100.0,
            ])
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);

        $this->assertTrue($agent->addTransactionMetric('SomeMetric', 100.0));
    }

    /**
     * @group unit
     * @covers ::disableAutomaticRealUserMonitoringScripts
     *
     * @return void
     */
    public function testDisableAutomaticRealUserMonitoringScripts(): void
    {
        $this->newAgentMock
            ->expects($this->once())
            ->method('__call')
            ->with('newrelic_disable_autorum')
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);

        $agent->disableAutomaticRealUserMonitoringScripts();
    }

    /**
     * @group unit
     * @covers ::getRealUserMonitoringHeaderScript
     * @covers ::getRealUserMonitoringFooterScript
     *
     * @return void
     */
    public function testGetRealUserMonitoringScripts(): void
    {
        $this->newAgentMock
            ->expects($this->any())
            ->method('__call')
            ->willReturnMap([
                ['newrelic_get_browser_timing_header', [false], 'echo "Header timing script!"'],
                ['newrelic_get_browser_timing_footer', [false], 'echo "Footer timing script!"'],
            ])
        ;

        $agent = new NewRelicAgent(null, $this->newAgentMock);

        $this->assertEquals('echo "Header timing script!"', $agent->getRealUserMonitoringHeaderScript());
        $this->assertEquals('echo "Footer timing script!"', $agent->getRealUserMonitoringFooterScript());
    }

    /**
     * @group unit
     * @covers ::createHttpClient
     *
     * @return void
     */
    public function testCreateHttpClient(): void
    {
        $agent = new NewRelicAgent('SomeLicense', $this->newAgentMock);

        $httpClient = $agent->createHttpClient();

        $defaults = [
            'headers' => [
                'X-License-Key' => 'SomeLicense',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        $this->assertInstanceOf(Client::class, $httpClient);
        $this->assertSame(NewRelicAgent::METRICS_BASE_URL, $httpClient->getConfig('base_url'));
        $this->assertSame($defaults, $httpClient->getConfig('defaults'));
    }

    /**
     * @return void
     */
    public function testIsSupported(): void
    {
        $this->assertEquals(extension_loaded(NewRelicAgent::EXTENSION_NAME), NewRelicAgent::isSupported());
    }
}
