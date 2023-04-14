<?php
declare(strict_types=1);
namespace Nouve\APM;

/**
 * Client
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM
 */
final class Client
{
    /** @var AgentInterface */
    private $agent;

    /**
     * @param AgentInterface $agent
     * @constructor
     */
    public function __construct(AgentInterface $agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return Client
     */
    public static function create(): self
    {
        $agentFactory = new AgentFactory();

        return new self($agentFactory->getSupported());
    }

    /**
     * @return AgentInterface
     */
    public function agent(): AgentInterface
    {
        return $this->agent;
    }

    /**
     * @return Browser
     */
    public function browser(): Browser
    {
        return new Browser($this->agent);
    }

    /**
     * @return Transaction
     */
    public function transaction(): Transaction
    {
        return new Transaction($this->agent);
    }

    /**
     * @return Transaction
     */
    public function startTransaction(): Transaction
    {
        return $this->agent->startTransaction();
    }
}
