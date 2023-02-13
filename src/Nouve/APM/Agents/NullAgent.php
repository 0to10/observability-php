<?php
declare(strict_types=1);
namespace Nouve\APM\Agents;

use GuzzleHttp\Client as HttpClient;
use Nouve\APM\AgentInterface;
use Nouve\APM\Transaction;

/**
 * NullAgent
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Agents
 */
final class NullAgent implements AgentInterface
{
    /**
     * @inheritdoc
     */
    public function setApplicationName(string $name): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function recordEvent(string $name, array $attributes): void
    {
    }

    /**
     * @inheritdoc
     */
    public function recordException(\Throwable $exception): void
    {
    }

    /**
     * @inheritdoc
     */
    public function startTransaction(): Transaction
    {
        return new Transaction($this);
    }

    /**
     * @inheritdoc
     */
    public function setTransactionName(string $name): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function addTransactionMetric(string $name, float $milliseconds): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function addTransactionParameter(string $name, $value): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function flagTransaction(bool $background): void
    {
    }

    /**
     * @inheritdoc
     */
    public function ignoreTransactionApdex(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function ignoreTransaction(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function stopTransactionTiming(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function endTransaction(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function disableAutomaticRealUserMonitoringScripts(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getRealUserMonitoringHeaderScript(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getRealUserMonitoringFooterScript(): string
    {
        return '';
    }

    /**
     * @return HttpClient
     */
    public function createHttpClient(): HttpClient
    {
        return new HttpClient();
    }

    /**
     * @inheritdoc
     */
    public static function isSupported(): bool
    {
        return true;
    }
}