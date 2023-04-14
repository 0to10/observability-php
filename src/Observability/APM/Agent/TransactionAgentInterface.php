<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM\Agent;

use Throwable;
use ZERO2TEN\Observability\APM\Datastore;
use ZERO2TEN\Observability\APM\TransactionInterface;

/**
 * TransactionAgentInterface
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM\Agent
 */
interface TransactionAgentInterface
{
    /**
     * Stops the current transaction and starts a new transaction. This method
     * must return a new TransactionInterface instance.
     *
     * @param bool $ignorePrevious
     * @return TransactionInterface
     */
    public function startTransaction(bool $ignorePrevious = false): TransactionInterface;

    /**
     * Changes the name for the current transaction.
     *
     * @param string $name
     * @return void
     */
    public function changeTransactionName(string $name): void;

    /**
     * @param string $name
     * @param bool|float|int|string $value
     * @return void
     */
    public function addTransactionParameter(string $name, $value): void;

    /**
     * If $background is `true` flags the current transaction as non-web, or as
     * web transaction otherwise.
     *
     * @param bool $background
     * @return void
     */
    public function markTransactionAsBackground(bool $background): void;

    /**
     * Records a Throwable (or Exception) instance that is not automatically
     * recorded. Note that only the last recorded instance will be used.
     *
     * @param string $message
     * @param Throwable $e
     * @return void
     */
    public function recordTransactionException(string $message, Throwable $e): void;

    /**
     * Adds a datastore (e.g. database) segment to the current transaction.
     *
     * Uses the result of the callable as the return for this method.
     *
     * @param Datastore $datastore
     * @param callable $callable
     * @param string|null $query
     * @param string|null $inputQueryLabel
     * @param string|null $inputQuery
     * @return mixed
     */
    public function addTransactionDatastoreSegment(
        Datastore $datastore,
        callable $callable,
        string $query = null,
        string $inputQueryLabel = null,
        string $inputQuery = null
    );

    /**
     * Stop timing this transaction, but continue instrumenting it.
     *
     * @return void
     */
    public function stopTransactionTiming(): void;

    /**
     * Ignore this transaction when calculating the Apdex.
     *
     * @return void
     */
    public function ignoreTransactionApdex(): void;

    /**
     * Do not instrument this transaction.
     *
     * @return void
     */
    public function ignoreTransaction(): void;

    /**
     * Returns `true` if this transaction was marked as "sampled".
     *
     * @return bool
     */
    public function isTransactionSampled(): bool;

    /**
     * Stop instrumenting this transaction immediately.
     *
     * @return void
     */
    public function endTransaction(): void;

    /**
     * Returns `true` if the transaction was successfully ended using the
     * "endTransaction" method.
     *
     * @return bool
     */
    public function isTransactionEnded(): bool;
}
