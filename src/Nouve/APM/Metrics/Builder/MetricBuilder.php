<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics\Builder;

use Nouve\APM\Metrics\Definition\ArrayMetricDefinition;
use Nouve\APM\Metrics\Definition\MetricDefinition;
use Nouve\APM\Metrics\Definition;

/**
 * MetricBuilder
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics\Builder
 */
final class MetricBuilder
{
    /** @var ArrayMetricDefinition */
    private $parent;
    /** @var MetricDefinition[] */
    private $mapping;

    /**
     * @constructor
     */
    public function __construct()
    {
        $this->mapping = array(
            'array' => ArrayMetricDefinition::class,
            'bytes' => Definition\ByteMetricDefinition::class,
            'percentage' => Definition\PercentageMetricDefinition::class,
            'scripts' => Definition\ScriptsMetricDefinition::class,
        );
    }

    /**
     * @param ArrayMetricDefinition $parent
     * @return self
     */
    public function setParent(ArrayMetricDefinition $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param string $name
     * @return ArrayMetricDefinition|MetricDefinition
     */
    public function category(string $name): MetricDefinition
    {
        return $this->definition($name, 'array');
    }

    /**
     * @param string $name
     * @return Definition\ByteMetricDefinition|MetricDefinition
     */
    public function byteMetric(string $name): MetricDefinition
    {
        return $this->definition($name, 'bytes');
    }

    /**
     * @param string $name
     * @return Definition\PercentageMetricDefinition|MetricDefinition
     */
    public function percentageMetric(string $name): MetricDefinition
    {
        return $this->definition($name, 'percentage');
    }

    /**
     * @param string $name
     * @return Definition\ScriptsMetricDefinition|MetricDefinition
     */
    public function scriptsMetric(string $name): MetricDefinition
    {
        return $this->definition($name, 'scripts');
    }

    /**
     * @param string $name
     * @param string $type
     * @throws \InvalidArgumentException
     * @return MetricDefinition
     */
    private function definition(string $name, string $type): MetricDefinition
    {
        if (!isset($this->mapping[$type])) {
            throw new \InvalidArgumentException(sprintf('Metric type "%s" is not registered.', $type));
        }

        $definition = new $this->mapping[$type]($name, $this);

        if (null !== $this->parent) {
            $this->parent->append($definition);
        }

        return $definition;
    }

    /**
     * @return ArrayMetricDefinition
     */
    public function end(): ArrayMetricDefinition
    {
        return $this->parent;
    }
}
