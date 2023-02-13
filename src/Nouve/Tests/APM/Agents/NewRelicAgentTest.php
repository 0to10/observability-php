<?php
declare(strict_types=1);
namespace Nouve\Tests\APM\Agents;

use GuzzleHttp\Client;
use Nouve\APM\Agents\NewRelicAgent;
use Nouve\APM\Transaction;
use PHPUnit\Framework\TestCase;
use PHPUnit\Extension\FunctionMocker;

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
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $php;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->php = FunctionMocker::start($this, 'Nouve\\APM\\Agents')
            ->mockFunction('extension_loaded')
            ->mockFunction('ini_get')
            ->mockFunction('newrelic_set_appname')

            ->mockFunction('newrelic_name_transaction')
            ->mockFunction('newrelic_background_job')
            ->mockFunction('newrelic_ignore_apdex')
            ->mockFunction('newrelic_ignore_transaction')
            ->mockFunction('newrelic_end_transaction')
            ->mockFunction('newrelic_end_of_transaction')
            ->mockFunction('newrelic_start_transaction')
            ->mockFunction('newrelic_custom_metric')
            ->mockFunction('newrelic_add_custom_parameter')

            ->mockFunction('newrelic_get_browser_timing_header')
            ->mockFunction('newrelic_get_browser_timing_footer')

            ->mockFunction('newrelic_notice_error')
            ->mockFunction('newrelic_record_custom_event')
            ->getMock()
        ;
    }

    /**
     * @group unit
     * @covers ::__construct
     *
     * @return void
     */
    public function testConstruct(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->at(1))
            ->method('ini_get')
            ->with('newrelic.appname')
            ->willReturn('Application name')
        ;

        $this->php
            ->expects($this->at(2))
            ->method('ini_get')
            ->with('newrelic.license')
            ->willReturn('0123456789ABC')
        ;

        new NewRelicAgent;
    }

    /**
     * @group unit
     * @covers ::__construct
     *
     * @return void
     */
    public function testConstructWithCustomLicense(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('ini_get')
            ->with('newrelic.appname')
            ->willReturn('Application name')
        ;

        new NewRelicAgent('SomeLicense');
    }

    /**
     * @group unit
     * @covers ::__construct
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage This agent is not supported on this platform.
     */
    public function testConstructWithUnsupportedPlatformThrowsException(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(false)
        ;

        $this->php
            ->expects($this->never())
            ->method('ini_get')
        ;

        new NewRelicAgent;
    }

    /**
     * @group unit
     * @covers ::stopTransactionTiming
     *
     * @return void
     */
    public function testStopTransactionTiming(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_end_of_transaction')
        ;

        (new NewRelicAgent)->stopTransactionTiming();
    }

    /**
     * @group unit
     * @covers ::endTransaction
     *
     * @return void
     */
    public function testEndTransaction(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->any())
            ->method('newrelic_end_transaction')
            ->with(false)
            ->willReturnOnConsecutiveCalls([
                true,
                false,
            ])
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_start_transaction')
            ->with($this->anything())
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent;

        $this->assertTrue($agent->endTransaction());
        $this->assertTrue($agent->endTransaction());

        $agent->startTransaction();

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
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_set_appname')
            ->with('SomeName', 'license-code', false)
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent('license-code');

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

        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_record_custom_event')
            ->with($eventName, $eventAttributes)
        ;

        (new NewRelicAgent)->recordEvent($eventName, $eventAttributes);
    }

    /**
     * @group unit
     * @covers ::recordEvent
     * @covers ::guardIsNotReservedWord
     *
     * @dataProvider reservedWordsProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Cannot use reserved word "\w+" as metric name\./
     *
     * @param string $reservedWord
     * @return void
     */
    public function testRecordEventWithReservedWordThrowsException(string $reservedWord): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->never())
            ->method('newrelic_record_custom_event')
        ;

        (new NewRelicAgent)->recordEvent($reservedWord, []);
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
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_name_transaction')
            ->with('MyTransaction')
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent;

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
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_ignore_transaction')
        ;

        (new NewRelicAgent)->ignoreTransaction();
    }

    /**
     * @group unit
     * @covers ::flagTransaction
     *
     * @return void
     */
    public function testFlagTransaction(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->any())
            ->method('newrelic_background_job')
            ->withConsecutive([
                true,
            ], [
                false,
            ])
        ;

        $agent = new NewRelicAgent;

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
        $exception = new \Exception('Some exception message.');

        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_notice_error')
            ->with($exception->getMessage(), $exception)
        ;

        (new NewRelicAgent)->recordException($exception);
    }


    /**
     * @group unit
     * @covers ::startTransaction
     *
     * @return void
     */
    public function testStartTransaction(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->any())
            ->method('newrelic_end_transaction')
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->any())
            ->method('newrelic_start_transaction')
            ->willReturnOnConsecutiveCalls([
                true,
                false,
            ])
        ;

        $agent = new NewRelicAgent;

        $this->assertTrue($agent->endTransaction());
        $this->assertInstanceOf(Transaction::class, $agent->startTransaction());

        $this->assertTrue($agent->endTransaction());
        $this->assertNotNull($agent->startTransaction());
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
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_add_custom_parameter')
            ->with('SomeParameter', 'test')
            ->willReturn(true)
        ;

        (new NewRelicAgent)->addTransactionParameter('SomeParameter', 'test');
    }

    /**
     * @group unit
     * @covers ::addTransactionParameter
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Transaction parameter value must be scalar, "\w+" given\./
     *
     * @return void
     */
    public function testAddTransactionParameterWithNonScalarThrowsException(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->never())
            ->method('newrelic_add_custom_parameter')
        ;

        (new NewRelicAgent)->addTransactionParameter('SomeParameter', new \stdClass);
    }

    /**
     * @return void
     */
    public function testIgnoreTransactionApdex(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_ignore_apdex')
        ;

        (new NewRelicAgent)->ignoreTransactionApdex();
    }

    /**
     * @group unit
     * @covers ::addTransactionMetric
     *
     * @return void
     */
    public function testAddTransactionMetric(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_custom_metric')
            ->with('Custom/SomeMetric', 100.0)
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent;

        $this->assertTrue($agent->addTransactionMetric('SomeMetric', 100.0));
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
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_get_browser_timing_header')
            ->with(false)
            ->willReturn('echo \'Header timing script!\'')
        ;

        $this->php
            ->expects($this->once())
            ->method('newrelic_get_browser_timing_footer')
            ->with(false)
            ->willReturn('echo \'Footer timing script!\'')
        ;

        $agent = new NewRelicAgent;

        $this->assertEquals('echo \'Header timing script!\'', $agent->getRealUserMonitoringHeaderScript());
        $this->assertEquals('echo \'Footer timing script!\'', $agent->getRealUserMonitoringFooterScript());
    }

    /**
     * @group unit
     * @covers ::createHttpClient
     *
     * @return void
     */
    public function testCreateHttpClient(): void
    {
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $agent = new NewRelicAgent('SomeLicense');

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
        $this->php
            ->expects($this->once())
            ->method('extension_loaded')
            ->with(NewRelicAgent::EXTENSION_NAME)
            ->willReturn(true)
        ;

        $supported = NewRelicAgent::isSupported();

        $this->assertEquals(true, $supported);
    }
}
