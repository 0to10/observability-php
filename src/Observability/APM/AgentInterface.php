<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM;

use ZERO2TEN\Observability\APM\Agent\BrowserAgentInterface;
use ZERO2TEN\Observability\APM\Agent\TransactionAgentInterface;

/**
 * AgentInterface
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM
 */
interface AgentInterface extends BrowserAgentInterface, TransactionAgentInterface
{
    /**
     * Sets a new APM application name, which controls data rollup. Changing the
     * application name will start a new transaction.
     *
     * @param string $name
     * @param bool $ignoreTransaction
     * @return void
     */
    public function changeApplicationName(string $name, bool $ignoreTransaction = true): void;

    /**
     * Enable or disable capturing of URL parameters.
     *
     * @param bool $enable
     * @return void
     */
    public function captureUrlParameters(bool $enable = true): void;

    /**
     * Record a custom event with the given name and attributes.
     *
     * @param string $name
     * @param array $attributes
     * @return void
     */
    public function recordCustomEvent(string $name, array $attributes): void;

    /**
     * Add a custom metric (in milliseconds) to the current transaction. Can be
     * used to time a specific component of the application, that is not captured
     * by default.
     *
     * @param string $name
     * @param float $milliseconds
     * @return void
     */
    public function addCustomMetric(string $name, float $milliseconds): void;

    /**
     * @param string $word
     * @return bool
     */
    public function isReservedWord(string $word): bool;

    /**
     * Returns `true` if the Agent is supported, `false` if it is not.
     *
     * @return bool
     */
    public function isSupported(): bool;
}
