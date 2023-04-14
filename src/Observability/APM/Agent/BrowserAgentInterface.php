<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM\Agent;

/**
 * BrowserAgentInterface
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM\Agent
 */
interface BrowserAgentInterface
{
    /**
     * Do not automatically inject the Javascript for page load timing.
     *
     * @return void
     */
    public function disableAutomaticBrowserMonitoringScripts(): void;

    /**
     * Returns the Javascript (without tags) for Real User Monitoring (RUM) that
     * needs to be placed just after the <head> open tag.
     *
     * @return string
     */
    public function getBrowserMonitoringHeaderScript(): string;

    /**
     * Returns the Javascript (without tags) for Real User Monitoring (RUM) that
     * needs to be placed just before the </body> closing tag.
     *
     * @return string
     */
    public function getBrowserMonitoringFooterScript(): string;
}
