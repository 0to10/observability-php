<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability\APM\Agent;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ZERO2TEN\Observability\APM\AgentInterface;
use ZERO2TEN\Observability\APM\FunctionProxy;
use ZERO2TEN\Observability\APM\Transaction;
use ZERO2TEN\Observability\APM\TransactionInterface;

/**
 * Agent
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability\APM\Agent
 */
abstract class Agent implements AgentInterface, LoggerAwareInterface
{
    use FunctionProxy;
    use LoggerAwareTrait;

    /** @var bool */
    private $initialised;

    /**
     * @constructor
     */
    final public function __construct()
    {
        $this->logger = new NullLogger();

        $this->initialised = $this->initialise();
    }

    /**
     * Initialises the Agent instance.
     *
     * Note that you *cannot* use the Logger in this method. When this method is
     * called, only a NullLogger instance will have been set - all calls to this
     * instance are discarded. Throw an exception (with caution) instead.
     *
     * @return bool
     */
    abstract protected function initialise(): bool;

    /**
     * This method serves as a proxy for calling functions for Agents.
     *
     * Extending classes must ensure that the arguments passed to the function
     * are correct, as this method will just pass through all arguments.
     *
     * Returns the result of the called function (which may be `false`), or `false`
     * on failure.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|false
     */
    public function __call(string $name, array $arguments = [])
    {
        if (!$this->initialised) {
            $this->logger->info('[APM] Client was not initialised.');
            return false;
        }

        return $this->proxyFunctionCall($name, $arguments);
    }

    /**
     * @param AgentInterface $agent
     * @return TransactionInterface
     */
    final public function createTransaction(AgentInterface $agent): TransactionInterface
    {
        return new Transaction($agent);
    }

    /**
     * @inheritDoc
     */
    final public function isSupported(): bool
    {
        return $this->initialised;
    }
}
