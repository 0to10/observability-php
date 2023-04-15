<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability;

use PHPUnit\Framework\MockObject\MockObject;
use ZERO2TEN\Observability\APM\AgentInterface;
use ZERO2TEN\Observability\APM\BrowserInterface;
use ZERO2TEN\Observability\APM\TransactionInterface;
use ZERO2TEN\Observability\Client;
use PHPUnit\Framework\TestCase;

/**
 * ClientTest
 *
 * @coversDefaultClass \ZERO2TEN\Observability\Client
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability
 */
class ClientTest extends TestCase
{
    /** @var AgentInterface|MockObject */
    private $agentMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->agentMock = $this->createMock(AgentInterface::class);
    }

    /**
     * @covers ::agent
     * @covers ::__construct
     * @group unit
     *
     * @return void
     */
    public function testAgent(): void
    {
        $client = new Client($this->agentMock);

        $this->assertSame($this->agentMock, $client->agent());
    }

    /**
     * @covers ::browser
     * @group unit
     *
     * @return void
     */
    public function testBrowser(): void
    {
        $client = new Client($this->agentMock);

        $this->assertInstanceOf(BrowserInterface::class, $client->browser());
    }

    /**
     * @covers ::transaction
     * @group unit
     *
     * @return void
     */
    public function testTransaction(): void
    {
        $client = new Client($this->agentMock);

        $this->assertInstanceOf(TransactionInterface::class, $client->transaction());
    }
}
