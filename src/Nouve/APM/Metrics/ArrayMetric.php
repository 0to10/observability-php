<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics;

/**
 * ArrayMetric
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics
 */
final class ArrayMetric extends Metric
{
    /** @var Metric[] */
    private $metrics = array();

    /**
     * @param Metric $metric
     * @throws \InvalidArgumentException
     * @return self
     */
    public function append(Metric $metric): self
    {
        $name = $metric->getName();

        if (0 === strlen($name)) {
            throw new \InvalidArgumentException('Metrics must be named.');
        }

        if (isset($this->metrics[$name])) {
            throw new \InvalidArgumentException(sprintf('A metric named "%s" was already added.', $name));
        }

        $this->metrics[$name] = $metric;
        return $this;
    }

    /**
     * @return Metric[]|iterable
     */
    public function getMetrics(): iterable
    {
        return $this->metrics;
    }

    /**
     * @inheritdoc
     */
    protected function finalizeValue($value): array
    {
        return (array)$value;
    }
}
