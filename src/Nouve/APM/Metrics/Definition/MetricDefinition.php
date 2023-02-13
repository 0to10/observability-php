<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics\Definition;

use Nouve\APM\Metrics\Builder\MetricBuilder;
use Nouve\APM\Metrics\Metric;

/**
 * Metric
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics\Definition
 */
abstract class MetricDefinition
{
    /** @var string */
    private $name;

    /** @var string */
    protected $label;

    /** @var MetricBuilder|null */
    private $parent;

    /**
     * @param string $name
     * @param MetricBuilder|null $parent
     * @constructor
     */
    public function __construct(string $name, MetricBuilder $parent = null)
    {
        $this->setName($name);
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     * @return static
     */
    public function setName(string $name): self
    {
        if (0 === strlen($name)) {
            throw new \InvalidArgumentException('A metric must be given a name.');
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return MetricBuilder|null
     */
    public function end(): ?MetricBuilder
    {
        return $this->parent;
    }

    /**
     * @return Metric
     */
    public function getMetric(): Metric
    {
        $metric = $this->createMetric();

        // TODO check if given value matches constraints

        // TODO normalize value

        // TODO set value on metric

        return $metric;
    }

    /**
     * @return Metric
     */
    abstract public function createMetric(): Metric;
}