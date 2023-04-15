<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM\Agent;

use Throwable;
use ZERO2TEN\Observability\APM\Datastore;
use ZERO2TEN\Observability\APM\TransactionInterface;

/**
 * NullAgent
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM\Agent
 */
final class NullAgent extends Agent
{
    /** @var bool */
    private $transactionEnded = false;

    /**
     * @inheritDoc
     */
    protected function initialise(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function changeApplicationName(string $name, bool $ignoreTransaction = true): void
    {
    }

    /**
     * @inheritDoc
     */
    public function captureUrlParameters(bool $enable = true): void
    {
    }

    /**
     * @inheritdoc
     */
    public function recordCustomEvent(string $name, array $attributes): void
    {
    }

    /**
     * @inheritdoc
     */
    public function addCustomMetric(string $name, float $milliseconds): void
    {
    }

    /**
     * @inheritdoc
     */
    public function disableAutomaticBrowserMonitoringScripts(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getBrowserMonitoringHeaderScript(): string
    {
        return '<!-- NullAgent - header.js -->';
    }

    /**
     * @inheritdoc
     */
    public function getBrowserMonitoringFooterScript(): string
    {
        return '<!-- NullAgent - footer.js -->';
    }

    /**
     * @inheritdoc
     */
    public function startTransaction(bool $ignorePrevious = false): TransactionInterface
    {
        $this->transactionEnded = false;

        return $this->createTransaction($this);
    }

    /**
     * @inheritdoc
     */
    public function changeTransactionName(string $name): void
    {
    }

    /**
     * @inheritdoc
     */
    public function addTransactionParameter(string $name, $value): void
    {
    }

    /**
     * @inheritdoc
     */
    public function markTransactionAsBackground(bool $background): void
    {
    }

    /**
     * @inheritdoc
     */
    public function recordTransactionException(string $message, Throwable $e): void
    {
    }

    /**
     * @inheritDoc
     */
    public function addTransactionDatastoreSegment(
        Datastore $datastore,
        callable $callable,
        string $query = null,
        string $inputQueryLabel = null,
        string $inputQuery = null
    )
    {
        return null;
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
     * @inheritDoc
     */
    public function isTransactionSampled(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function endTransaction(): void
    {
        $this->transactionEnded = true;
    }

    /**
     * @inheritDoc
     */
    public function isTransactionEnded(): bool
    {
        return $this->transactionEnded;
    }
}
