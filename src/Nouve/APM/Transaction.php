<?php
declare(strict_types=1);
namespace Nouve\APM;

/**
 * Transaction
 *
 * This class exposes specific methods of the AgentInterface that allows
 * customization of the (current) transaction.
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM
 */
final class Transaction
{
    /** @var AgentInterface */
    private $agent;
    /** @var bool */
    private $ignored = false;

    /**
     * @param AgentInterface $agent
     * @constructor
     */
    public function __construct(AgentInterface $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Set custom name for this transaction.
     *
     * @param string $name
     * @throws \RuntimeException
     * @return Transaction
     */
    public function setName(string $name): self
    {
        if (!$this->agent->setTransactionName($name)) {
            throw new \RuntimeException('Unable to change the transaction name.');
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $milliseconds
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return Transaction
     */
    public function addMetric(string $name, $milliseconds): self
    {
        if (is_int($milliseconds)) {
            $milliseconds = (float)$milliseconds;
        }

        if (!is_float($milliseconds)) {
            throw new \InvalidArgumentException(sprintf(
                'Transaction metric value must be an integer or float, "%s" given.',
                gettype($milliseconds)
            ));
        }

        if (!$this->agent->addTransactionMetric($name, $milliseconds)) {
            throw new \RuntimeException('Unable to add metric to the transaction.');
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \RuntimeException
     * @return Transaction
     */
    public function addParameter(string $name, $value): self
    {
        if (!$this->agent->addTransactionParameter($name, $value)) {
            throw new \RuntimeException('Unable to add parameter to the transaction.');
        }

        return $this;
    }

    /**
     * Flag this transaction as a "background" transaction.
     *
     * @return Transaction
     */
    public function flagBackground(): self
    {
        $this->agent->flagTransaction(true);
        return $this;
    }

    /**
     * Flag this transaction as a "web" transaction.
     *
     * @return Transaction
     */
    public function flagWeb(): self
    {
        $this->agent->flagTransaction(false);
        return $this;
    }

    /**
     * Do not instrument this transaction.
     *
     * @return Transaction
     */
    public function ignore(): self
    {
        $this->agent->ignoreTransaction();
        $this->ignored = true;

        return $this;
    }

    /**
     * Ignore this transaction when calculating the Apdex.
     *
     * @return Transaction
     */
    public function ignoreApdex(): self
    {
        if ($this->ignored) {
            return $this;
        }

        $this->agent->ignoreTransactionApdex();

        return $this;
    }

    /**
     * Stop timing this transaction, but continue instrumenting it.
     *
     * @return Transaction
     */
    public function stopTiming(): self
    {
        $this->agent->stopTransactionTiming();
        return $this;
    }

    /**
     * Stop instrumenting this transaction immediately.
     *
     * @return AgentInterface
     */
    public function end(): AgentInterface
    {
        $this->agent->endTransaction();
        return $this->agent;
    }
}
