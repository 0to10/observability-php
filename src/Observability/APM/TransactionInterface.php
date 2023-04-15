<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM;

use Throwable;
use ZERO2TEN\Observability\APM\Agent\TransactionAgentInterface;

/**
 * TransactionInterface
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM
 */
interface TransactionInterface
{
    /**
     * @see TransactionAgentInterface::startTransaction()
     *
     * @param bool $ignorePrevious
     * @return TransactionInterface
     */
    public function start(bool $ignorePrevious = false): TransactionInterface;

    /**
     * @see TransactionAgentInterface::changeTransactionName()
     *
     * @param string $name
     * @return TransactionInterface
     */
    public function changeName(string $name): TransactionInterface;

    /**
     * @see TransactionAgentInterface::addTransactionParameter()
     *
     * @param string $name
     * @param bool|float|int|string $value
     * @return TransactionInterface
     */
    public function addParameter(string $name, $value): TransactionInterface;

    /**
     * @param array<string, bool|float|int|string> $parameters
     * @return TransactionInterface
     */
    public function addParameters(array $parameters): TransactionInterface;

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @see TransactionAgentInterface::markTransactionAsBackground()
     *
     * @param bool $background
     * @return TransactionInterface
     */
    public function markAsBackground(bool $background): TransactionInterface;

    /**
     * @see TransactionAgentInterface::recordTransactionException()
     *
     * @param Throwable $e
     * @return TransactionInterface
     */
    public function recordException(Throwable $e): TransactionInterface;

    /**
     * @see TransactionAgentInterface::addTransactionDatastoreSegment()
     *
     * @param Datastore $datastore
     * @param callable $callable
     * @param string|null $query
     * @param string|null $inputQueryLabel
     * @param string|null $inputQuery
     * @return mixed
     */
    public function addDatastoreSegment(
        Datastore $datastore,
        callable $callable,
        string $query = null,
        string $inputQueryLabel = null,
        string $inputQuery = null
    );

    /**
     * @see TransactionAgentInterface::stopTransactionTiming()
     *
     * @return TransactionInterface
     */
    public function stopTiming(): TransactionInterface;

    /**
     * @see TransactionAgentInterface::ignoreTransactionApdex()
     *
     * @return TransactionInterface
     */
    public function ignoreApdex(): TransactionInterface;

    /**
     * @see TransactionAgentInterface::ignoreTransaction()
     *
     * @return TransactionInterface
     */
    public function ignore(): TransactionInterface;

    /**
     * Returns `true` if this transaction was ignored.
     *
     * @return bool
     */
    public function isIgnored(): bool;

    /**
     * @see TransactionAgentInterface::isTransactionSampled()
     *
     * @return bool
     */
    public function isSampled(): bool;

    /**
     * @see TransactionAgentInterface::endTransaction()
     *
     * @return TransactionInterface
     */
    public function end(): TransactionInterface;

    /**
     * @see TransactionAgentInterface::isTransactionEnded()
     *
     * @return bool
     */
    public function isEnded(): bool;
}
