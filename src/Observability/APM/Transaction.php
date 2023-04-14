<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM;

use Throwable;

/**
 * Transaction
 *
 * This class exposes specific methods of the AgentInterface that allows
 * customization of the (current) transaction.
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM
 */
final class Transaction implements TransactionInterface
{
    /** @var AgentInterface */
    private $agent;

    /**
     * Indicates if the current transaction was ignored.
     *
     * @var bool
     */
    private $ignored = false;

    /**
     * List of custom parameters that were added to the current transaction.
     *
     * Note that this will only contain parameters that were added via this
     * class - manually added transactions will not be included.
     *
     * @var array<string, mixed>
     */
    private $parameters = [];

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
    public function start(bool $ignorePrevious = false): TransactionInterface
    {
        if ($ignorePrevious) {
            $this->ignore();
        }

        $this->end();

        return $this->agent->startTransaction();
    }

    /**
     * @inheritDoc
     */
    public function changeName(string $name): TransactionInterface
    {
        $this->agent->changeTransactionName($name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addParameter(string $name, $value): TransactionInterface
    {
        $this->agent->addTransactionParameter($name, $value);
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addParameters(array $parameters): TransactionInterface
    {
        foreach ($parameters as $name => $value) {
            $this->addParameter($name, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function markAsBackground(bool $background): TransactionInterface
    {
        $this->agent->markTransactionAsBackground($background);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function recordException(Throwable $e): TransactionInterface
    {
        $this->agent->recordTransactionException($e->getMessage(), $e);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addDatastoreSegment(
        Datastore $datastore,
        callable $callable,
        string $query = null,
        string $inputQueryLabel = null,
        string $inputQuery = null
    )
    {
        return $this->agent->addTransactionDatastoreSegment(
            $datastore,
            $callable,
            $query,
            $inputQueryLabel,
            $inputQuery
        );
    }

    /**
     * @inheritDoc
     */
    public function stopTiming(): TransactionInterface
    {
        $this->agent->stopTransactionTiming();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function ignoreApdex(): TransactionInterface
    {
        $this->agent->ignoreTransactionApdex();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function ignore(): TransactionInterface
    {
        $this->agent->ignoreTransaction();
        $this->ignored = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isIgnored(): bool
    {
        return $this->ignored;
    }

    /**
     * @inheritDoc
     */
    public function isSampled(): bool
    {
        return $this->agent->isTransactionSampled();
    }

    /**
     * @inheritDoc
     */
    public function end(): TransactionInterface
    {
        $this->agent->endTransaction();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEnded(): bool
    {
        return $this->agent->isTransactionEnded();
    }
}
