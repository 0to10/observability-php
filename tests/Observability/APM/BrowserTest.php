<?php
declare(strict_types=1);
namespace ZERO2TEN\Tests\Observability\APM;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZERO2TEN\Observability\APM\AgentInterface;
use ZERO2TEN\Observability\APM\Browser;
use ZERO2TEN\Observability\APM\BrowserInterface;

/**
 * BrowserTest
 *
 * @coversDefaultClass \ZERO2TEN\Observability\APM\Browser
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Tests\Observability\APM
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

        $this->agent = $this->createMock(AgentInterface::class);

        $this->browser = new Browser($this->agent);
    }

    /**
     * @covers ::__construct
     * @group unit
     *
     * @return void
     */
    public function testBrowser(): void
    {
        $this->assertInstanceOf(BrowserInterface::class, $this->browser);
    }

    /**
     * @covers ::disableAutomaticTimingScripts
     * @group unit
     *
     * @return void
     */
    public function testDisableAutomaticTimingScripts(): void
    {
        $this->agent->expects($this->once())->method('disableAutomaticBrowserMonitoringScripts');

        $this->browser->disableAutomaticTimingScripts();
    }

    /**
     * @covers ::getHeaderScript
     * @group unit
     *
     * @return void
     */
    public function testGetHeaderScript(): void
    {
        $this->agent->expects($this->once())->method('getBrowserMonitoringHeaderScript')->willReturn('test');

        $this->assertEquals('test', $this->browser->getHeaderScript());
    }

    /**
     * @covers ::getFooterScript
     * @group unit
     *
     * @return void
     */
    public function testGetFooterScript(): void
    {
        $this->agent->expects($this->once())->method('getBrowserMonitoringFooterScript')->willReturn('test');

        $this->assertEquals('test', $this->browser->getFooterScript());
    }
}
