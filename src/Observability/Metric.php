<?php
declare(strict_types=1);
namespace ZERO2TEN\Observability;

use ZERO2TEN\Observability\APM\AgentInterface;

/**
 * Metric
 *
 * @copyright Copyright (c) 2023 0TO10 B.V. <https://0to10.nl>
 * @package ZERO2TEN\Observability
 */
class Metric
{
    /** @var AgentInterface */
    private $agent;
    /** @var string */
    private $name;

    /**
     * @param AgentInterface $agent
     * @param string $name
     * @constructor
     */
    public function __construct(AgentInterface $agent, string $name)
    {
        $this->agent = $agent;
        $this->name = $name;
    }
}