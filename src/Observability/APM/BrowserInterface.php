<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM;

use ZERO2TEN\Observability\APM\Agent\BrowserAgentInterface;

/**
 * BrowserInterface
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM
 */
interface BrowserInterface
{
    /**
     * @see BrowserAgentInterface::disableAutomaticBrowserMonitoringScripts()
     *
     * @return void
     */
    public function disableAutomaticTimingScripts(): void;

    /**
     * @see BrowserAgentInterface::getBrowserMonitoringHeaderScript()
     *
     * @return string
     */
    public function getHeaderScript(): string;

    /**
     * @see BrowserAgentInterface::getBrowserMonitoringFooterScript()
     *
     * @return string
     */
    public function getFooterScript(): string;
}
