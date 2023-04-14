<?php
declare(strict_types=1);
namespace Nouve\Tests\APM;

use InvalidArgumentException;
use Nouve\APM\AgentInterface;
use Nouve\APM\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * TransactionTest
 *
 * @coversDefaultClass \Nouve\APM\Transaction
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\Tests\APM
 */
class TransactionTest extends TestCase
{
    /** @var AgentInterface|MockObject */
    private $agent;
    /** @var Transaction */
    private $transaction;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = $this
            ->getMockBuilder(AgentInterface::class)
            ->getMock()
        ;

        $this->transaction = new Transaction($this->agent);
    }

    /**
     * @group unit
     * @covers ::setName
     *
     * @return void
     */
    public function testSetName(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('setTransactionName')
            ->with('SomeName')
            ->willReturn(true)
        ;

        $this->assertSame($this->transaction, $this->transaction->setName('SomeName'));
    }

    /**
     * @group unit
     * @covers ::setName
     *
     * @return void
     */
    public function testSetNameFailsThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to change the transaction name.');

        $this->agent
            ->expects($this->once())
            ->method('setTransactionName')
            ->with('SomeName')
            ->willReturn(false)
        ;

        $this->transaction->setName('SomeName');
    }

    /**
     * @group unit
     * @covers ::addMetric
     *
     * @return void
     */
    public function testAddMetric(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('addTransactionMetric')
            ->with('SomeMetric', 100.0)
            ->willReturn(true)
        ;

        $this->assertSame($this->transaction, $this->transaction->addMetric('SomeMetric', 100.0));
    }

    /**
     * @group unit
     * @covers ::addMetric
     *
     * @return void
     */
    public function testAddMetricInteger(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('addTransactionMetric')
            ->with('SomeMetric', 100.0)
            ->willReturn(true)
        ;

        $this->assertSame($this->transaction, $this->transaction->addMetric('SomeMetric', 100));
    }

    /**
     * @group unit
     * @covers ::addMetric
     *
     * @return void
     */
    public function testAddMetricFailsThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to add metric to the transaction.');

        $this->agent
            ->expects($this->once())
            ->method('addTransactionMetric')
            ->with('SomeMetric', 100.0)
            ->willReturn(false)
        ;

        $this->transaction->addMetric('SomeMetric', 100);
    }

    /**
     * @group unit
     * @covers ::addMetric
     *
     * @return void
     */
    public function testAddMetricWithStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction metric value must be an integer or float, "string" given.');

        $this->agent
            ->expects($this->never())
            ->method('addTransactionMetric')
        ;

        $this->transaction->addMetric('SomeMetric', 'test');
    }

    /**
     * @group unit
     * @covers ::addParameter
     *
     * @return void
     */
    public function testAddParameter(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('addTransactionParameter')
            ->with('SomeParameter', 'test')
            ->willReturn(true)
        ;

        $this->assertSame($this->transaction, $this->transaction->addParameter('SomeParameter', 'test'));
    }

    /**
     * @group unit
     * @covers ::addParameter
     *
     * @return void
     */
    public function testAddParameterFailsThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to add parameter to the transaction.');

        $this->agent
            ->expects($this->once())
            ->method('addTransactionParameter')
            ->with('SomeParameter', 'test')
            ->willReturn(false)
        ;

        $this->transaction->addParameter('SomeParameter', 'test');
    }

    /**
     * @group unit
     * @covers ::flagBackground
     *
     * @return void
     */
    public function testFlagBackground(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('flagTransaction')
            ->with(true)
        ;

        $this->assertSame($this->transaction, $this->transaction->flagBackground());
    }

    /**
     * @group unit
     * @covers ::flagWeb
     *
     * @return void
     */
    public function testFlagWeb(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('flagTransaction')
            ->with(false)
        ;

        $this->assertSame($this->transaction, $this->transaction->flagWeb());
    }

    /**
     * @group unit
     * @covers ::ignore
     *
     * @return void
     */
    public function testIgnore(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('ignoreTransaction')
        ;

        $this->assertSame($this->transaction, $this->transaction->ignore());
    }

    /**
     * @group unit
     * @covers ::ignoreApdex
     *
     * @return void
     */
    public function testIgnoreApdex(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('ignoreTransactionApdex')
        ;

        $this->assertSame($this->transaction, $this->transaction->ignoreApdex());
    }

    /**
     * @group unit
     * @covers ::ignoreApdex
     *
     * @return void
     */
    public function testIgnoreApdexIgnoredTransaction(): void
    {
        $this->agent
            ->expects($this->never())
            ->method('ignoreTransactionApdex')
        ;

        $this->transaction->ignore();

        $this->assertSame($this->transaction, $this->transaction->ignoreApdex());
    }

    /**
     * @group unit
     * @covers ::stopTiming
     *
     * @return void
     */
    public function testStopTiming(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('stopTransactionTiming')
        ;

        $this->assertSame($this->transaction, $this->transaction->stopTiming());
    }

    /**
     * @group unit
     * @covers ::end
     *
     * @return void
     */
    public function testEnd(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('endTransaction')
        ;

        $this->assertSame($this->agent, $this->transaction->end());
    }
}
