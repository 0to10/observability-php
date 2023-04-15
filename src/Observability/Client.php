<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability;

use ZERO2TEN\Observability\APM\AgentInterface;
use ZERO2TEN\Observability\APM\Browser;
use ZERO2TEN\Observability\APM\BrowserInterface;
use ZERO2TEN\Observability\APM\Transaction;
use ZERO2TEN\Observability\APM\TransactionInterface;

/**
 * Client
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability
 */
class Client
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
     * @return AgentInterface
     */
    public function agent(): AgentInterface
    {
        return $this->agent;
    }

    /**
     * @return BrowserInterface
     */
    public function browser(): BrowserInterface
    {
        return new Browser($this->agent);
    }

    /**
     * @return TransactionInterface
     */
    public function transaction(): TransactionInterface
    {
        return new Transaction($this->agent);
    }
}
