<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics\Definition;

use Nouve\APM\Metrics\Builder\MetricBuilder;
use Nouve\APM\Metrics\Metric;
use Nouve\APM\Metrics\ArrayMetric;

/**
 * ArrayMetricDefinition
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics\Definition
 */
final class ArrayMetricDefinition extends MetricDefinition
{
    /** @var MetricDefinition[] */
    private $children = array();

    /**
     * @param MetricDefinition $definition
     * @return self
     */
    public function append(MetricDefinition $definition): self
    {
        $this->children[$definition->getName()] = $definition;
        return $this;
    }

    /**
     * @return MetricBuilder
     */
    public function metrics(): MetricBuilder
    {
        return (new MetricBuilder)->setParent($this);
    }

    /**
     * @return MetricDefinition[]|iterable
     */
    public function children(): iterable
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function createMetric(): Metric
    {
        $metric = new ArrayMetric($this->getName(), 'array');
        foreach ($this->children as $child) {
            $metric->append($child->createMetric());
        }

        return $metric;
    }
}