<?php
declare(strict_types=1);
namespace Nouve\APM;

/**
 * Browser
 *
 * @copyright Copyright (c) 2019 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM
 */
final class Browser
{
    /** @var AgentInterface */
    private $agent;

    /**
     * @param AgentInterface $agent
     * @constructor
     */
    public function __construct(AgentInterface $agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return void
     */
    public function disableAutomaticTimingScripts(): void
    {
        $this->agent->disableAutomaticRealUserMonitoringScripts();
    }

    /**
     * @return string
     */
    public function getHeaderScript(): string
    {
        return $this->agent->getRealUserMonitoringHeaderScript();
    }

    /**
     * @return string
     */
    public function getFooterScript(): string
    {
        return $this->agent->getRealUserMonitoringFooterScript();
    }
}