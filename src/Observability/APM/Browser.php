<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM;

/**
 * Browser
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM
 */
class Browser implements BrowserInterface
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
     * @inheritDoc
     */
    public function disableAutomaticTimingScripts(): void
    {
        $this->agent->disableAutomaticBrowserMonitoringScripts();
    }

    /**
     * @inheritDoc
     */
    public function getHeaderScript(): string
    {
        return $this->agent->getBrowserMonitoringHeaderScript();
    }

    /**
     * @inheritDoc
     */
    public function getFooterScript(): string
    {
        return $this->agent->getBrowserMonitoringFooterScript();
    }
}
