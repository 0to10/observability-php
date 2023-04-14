<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability\APM\Agent;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ZERO2TEN\Observability\APM\Agent\Agent;
use ZERO2TEN\Observability\APM\TransactionInterface;

/**
 * @return string
 */
function stub(): string {
    return '"stub" was called';
}

/**
 * AgentTest
 *
 * @coversDefaultClass \ZERO2TEN\Observability\APM\Agent\Agent
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Tests\Observability\APM\Agent
 */
class AgentTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::isSupported
     * @group unit
     *
     * @throws ReflectionException
     * @return void
     */
    public function testAgent(): void
    {
        /** @var Agent|MockObject $agentMock */
        $agentMock = $this->getMockForAbstractClass(Agent::class, [], '', false);

        $agentMock
            ->expects($this->once())
            ->method('initialise')
            ->willReturn(true)
        ;

        $rc = new ReflectionClass($agentMock);
        $rc->getConstructor()->invoke($agentMock);

        $this->assertTrue($agentMock->isSupported());
    }

    /**
     * @covers ::__call
     * @group unit
     *
     * @throws ReflectionException
     * @return void
     */
    public function testCall(): void
    {
        /** @var LoggerInterface|MockObject $loggerMock */
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())->method($this->anything());

        /** @var Agent|MockObject $agentMock */
        $agentMock = $this->getMockForAbstractClass(Agent::class, [], '', false);
        $agentMock->expects($this->once())->method('initialise')->willReturn(true);

        $rc = new ReflectionClass($agentMock);
        $rc->getConstructor()->invoke($agentMock);

        $agentMock->setLogger($loggerMock);

        $this->assertSame('"stub" was called', $agentMock->__call(__NAMESPACE__ . '\stub'));
    }

    /**
     * @covers ::__call
     * @group unit
     *
     * @return void
     */
    public function testCallUninitialised(): void
    {
        /** @var LoggerInterface|MockObject $loggerMock */
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())->method('info')->with($this->isType('string'));

        /** @var Agent|MockObject $agentMock */
        $agentMock = $this->getMockForAbstractClass(Agent::class);
        $agentMock->setLogger($loggerMock);

        $this->assertFalse($agentMock->__call(__NAMESPACE__ . '\stub'));
    }

    /**
     * @covers ::createTransaction
     * @group unit
     *
     * @return void
     */
    public function testCreateTransaction(): void
    {
        /** @var Agent|MockObject $agentMock */
        $agentMock = $this->getMockForAbstractClass(Agent::class);

        $transaction = $agentMock->createTransaction($agentMock);

        $this->assertInstanceOf(TransactionInterface::class, $transaction);
    }
}
