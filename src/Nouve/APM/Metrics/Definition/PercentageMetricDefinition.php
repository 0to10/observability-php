<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics\Definition;

use Nouve\APM\Metrics\FloatMetric;
use Nouve\APM\Metrics\Metric;

/**
 * PercentageMetricDefinition
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics\Definition
 */
final class PercentageMetricDefinition extends MetricDefinition
{
    /**
     * @inheritdoc
     */
    public function createMetric(): Metric
    {
        return new FloatMetric($this->getName(), 'percentage');
    }
}
