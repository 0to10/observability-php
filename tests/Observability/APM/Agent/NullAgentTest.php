<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability\APM\Agent;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZERO2TEN\Observability\APM\Agent\NullAgent;
use ZERO2TEN\Observability\APM\AgentInterface;
use ZERO2TEN\Observability\APM\Datastore;
use ZERO2TEN\Observability\APM\TransactionInterface;
use ZERO2TEN\Tests\Observability\GenericDataProviders;

/**
 * NullAgentTest
 *
 * @coversDefaultClass \ZERO2TEN\Observability\APM\Agent\NullAgent
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Tests\Observability\APM\Agent
 */
class NullAgentTest extends TestCase
{
    use GenericDataProviders;

    /** @var AgentInterface */
    private $agent;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new NullAgent();
    }

    /**
     * @covers ::initialise
     * @group unit
     *
     * @return void
     */
    public function testInitialise(): void
    {
        $this->assertTrue($this->agent->isSupported());
    }

    /**
     * @covers ::changeApplicationName
     * @group unit
     *
     * @return void
     */
    public function testChangeApplicationName(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->changeApplicationName('new name'));
    }

    /**
     * @covers ::captureUrlParameters
     * @group unit
     *
     * @return void
     */
    public function testCaptureUrlParameters(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->captureUrlParameters());
    }

    /**
     * @covers ::recordCustomEvent
     * @group unit
     *
     * @return void
     */
    public function testRecordCustomEvent(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->recordCustomEvent('custom-event', []));
    }

    /**
     * @covers ::addCustomMetric
     * @group unit
     *
     * @return void
     */
    public function testAddCustomMetric(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->addCustomMetric('custom-metric', 14.0));
    }

    /**
     * @covers ::disableAutomaticBrowserMonitoringScripts
     * @group unit
     *
     * @return void
     */
    public function testDisableAutomaticBrowserMonitoringScripts(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->disableAutomaticBrowserMonitoringScripts());
    }

    /**
     * @covers ::getBrowserMonitoringHeaderScript
     * @group unit
     *
     * @return void
     */
    public function testGetBrowserMonitoringHeaderScript(): void
    {
        $this->assertSame('<!-- NullAgent - header.js -->', $this->agent->getBrowserMonitoringHeaderScript());
    }

    /**
     * @covers ::getBrowserMonitoringFooterScript
     * @group unit
     *
     * @return void
     */
    public function testGetBrowserMonitoringFooterScript(): void
    {
        $this->assertSame('<!-- NullAgent - footer.js -->', $this->agent->getBrowserMonitoringFooterScript());
    }

    /**
     * @covers ::startTransaction
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $ignore
     * @return void
     */
    public function testStartTransaction(bool $ignore): void
    {
        $transaction = $this->agent->startTransaction($ignore);

        $this->assertInstanceOf(TransactionInterface::class, $transaction);
        $this->assertFalse($transaction->isEnded());
    }

    /**
     * @covers ::changeTransactionName
     * @group unit
     *
     * @return void
     */
    public function testChangeTransactionName(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->changeTransactionName('name'));
    }

    /**
     * @covers ::addTransactionParameter
     * @group unit
     *
     * @return void
     */
    public function testAddTransactionParameter(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->addTransactionParameter('param', true));
    }

    /**
     * @covers ::markTransactionAsBackground
     * @group unit
     *
     * @dataProvider booleanDataProvider
     *
     * @param bool $background
     * @return void
     */
    public function testMarkTransactionAsBackground(bool $background): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->markTransactionAsBackground($background));
    }

    /**
     * @covers ::recordTransactionException
     * @group unit
     *
     * @return void
     */
    public function testRecordTransactionException(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->recordTransactionException('', new Exception()));
    }

    /**
     * @covers ::addTransactionDatastoreSegment
     * @group unit
     *
     * @return void
     */
    public function testAddTransactionDatastoreSegment(): void
    {
        /** @var Datastore|MockObject $datastoreMock */
        $datastoreMock = $this->createMock(Datastore::class);

        $result = $this->agent->addTransactionDatastoreSegment(
            $datastoreMock,
            static function () {},
            'sql',
            'query-label',
            'query'
        );

        $this->assertNull($result);
    }

    /**
     * @covers ::stopTransactionTiming
     * @group unit
     *
     * @return void
     */
    public function testStopTransactionTiming(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->stopTransactionTiming());
    }

    /**
     * @covers ::ignoreTransactionApdex
     * @group unit
     *
     * @return void
     */
    public function testIgnoreTransactionApdex(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->ignoreTransactionApdex());
    }

    /**
     * @covers ::ignoreTransaction
     * @group unit
     *
     * @return void
     */
    public function testIgnoreTransaction(): void
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->agent->ignoreTransaction());
    }

    /**
     * @covers ::isTransactionSampled
     * @group unit
     *
     * @return void
     */
    public function testIsTransactionSampled(): void
    {
        $this->assertTrue($this->agent->isTransactionSampled());
    }

    /**
     * @covers ::endTransaction
     * @covers ::isTransactionEnded
     * @group unit
     *
     * @return void
     */
    public function testEndTransaction(): void
    {
        $this->assertFalse($this->agent->isTransactionEnded());

        $this->agent->endTransaction();
        $this->assertTrue($this->agent->isTransactionEnded());
    }
}
