<?php
declare(strict_types=1);
namespace Nouve\APM;

use GuzzleHttp\Client as HttpClient;

/**
 * AgentInterface
 *
 * @deprecated Use \ZERO2TEN\Observability\APM\AgentInterface instead
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM
 */
interface AgentInterface
{
    /**
     * Sets the APM application name, which controls data rollup.
     *
     * @param string $name
     * @return bool
     */
    public function setApplicationName(string $name): bool;

    /**
     * Record a custom event with the given name and attributes.
     *
     * @param string $name
     * @param array $attributes
     * @return void
     */
    public function recordEvent(string $name, array $attributes): void;

    /**
     * Record an exception not captured by the agent by default (e.g. caught
     * exceptions).
     *
     * @param \Throwable $exception
     * @return void
     */
    public function recordException(\Throwable $exception): void;

    /**
     * @throws \LogicException
     * @return Transaction
     */
    public function startTransaction(): Transaction;

    /**
     * Set custom name for current transaction.
     *
     * @param string $name
     * @return bool
     */
    public function setTransactionName(string $name): bool;

    /**
     * Add a custom metric (in milliseconds) to the current transaction. Can be
     * used to time a specific component of the application, that is not captured
     * by default.
     *
     * @param string $name
     * @param float $milliseconds
     * @return bool
     */
    public function addTransactionMetric(string $name, float $milliseconds): bool;

    /**
     * Attaches a custom attribute to the current transaction.
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function addTransactionParameter(string $name, $value): bool;

    /**
     * Manually specify that a transaction is a background job or a web
     * transaction.
     *
     * @param bool $background
     * @return void
     */
    public function flagTransaction(bool $background): void;

    /**
     * Ignore the current transaction when calculating Application Performance
     * Index (Apdex).
     *
     * @return void
     */
    public function ignoreTransactionApdex(): void;

    /**
     * Do not instrument the current transaction.
     *
     * @return void
     */
    public function ignoreTransaction(): void;

    /**
     * Stop timing the current transaction, but continue instrumenting it.
     *
     * @return void
     */
    public function stopTransactionTiming(): void;

    /**
     * Stop instrumenting the current transaction immediately.
     *
     * @return bool
     */
    public function endTransaction(): bool;

    /**
     * Do not automatically inject the Javascript for page load timing.
     *
     * @return void
     */
    public function disableAutomaticRealUserMonitoringScripts(): void;

    /**
     * Returns the Javascript (without tags) for Real User Monitoring (RUM) that
     * needs to be placed just after the <head> open tag.
     *
     * @return string
     */
    public function getRealUserMonitoringHeaderScript(): string;

    /**
     * Returns the Javascript (without tags) for Real User Monitoring (RUM) that
     * needs to be placed just before the </body> closing tag.
     *
     * @return string
     */
    public function getRealUserMonitoringFooterScript(): string;

    /**
     * @return HttpClient
     */
    public function createHttpClient(): HttpClient;

    /**
     * Returns whether the AgentInterface implementation is supported or not.
     *
     * @return bool
     */
    public static function isSupported(): bool;
}
