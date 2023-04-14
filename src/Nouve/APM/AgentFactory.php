<?php
declare(strict_types=1);
namespace Nouve\APM;

use Nouve\APM\Agents\NewRelicAgent;
use Nouve\APM\Agents\NullAgent;

/**
 * AgentFactory
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM
 */
final class AgentFactory
{
    /** @var AgentInterface[] */
    private $agents = [
        'new_relic' => NewRelicAgent::class,
    ];

    /**
     * @return AgentInterface
     */
    public function getSupported(): AgentInterface
    {
        foreach ($this->agents as $agent) {
            if (!$agent::isSupported()) {
                continue;
            }

            return new $agent;
        }

        return new NullAgent;
    }
}
