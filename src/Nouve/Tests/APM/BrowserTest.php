<?php
declare(strict_types=1);
namespace Nouve\Tests\APM;

use Nouve\APM\AgentInterface;
use Nouve\APM\Browser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * BrowserTest
 *
 * @coversDefaultClass \Nouve\APM\Browser
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\Tests\APM
 */
class BrowserTest extends TestCase
{
    /** @var AgentInterface|MockObject */
    private $agent;
    /** @var Browser */
    private $browser;

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

        $this->browser = new Browser($this->agent);
    }

    /**
     * @group unit
     * @covers ::getHeaderScript
     * @covers ::__construct
     *
     * @return void
     */
    public function testGetHeaderScript(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('getRealUserMonitoringHeaderScript')
            ->willReturn('test')
        ;

        $this->assertEquals('test', $this->browser->getHeaderScript());
    }

    /**
     * @group unit
     * @covers ::getFooterScript
     * @covers ::__construct
     *
     * @return void
     */
    public function testGetFooterScript(): void
    {
        $this->agent
            ->expects($this->once())
            ->method('getRealUserMonitoringFooterScript')
            ->willReturn('test')
        ;

        $this->assertEquals('test', $this->browser->getFooterScript());
    }
}
