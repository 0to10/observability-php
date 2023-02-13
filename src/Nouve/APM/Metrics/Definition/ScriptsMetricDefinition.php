<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics\Definition;

use Nouve\APM\Metrics\IntegerMetric;
use Nouve\APM\Metrics\Metric;

/**
 * ScriptsMetricDefinition
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics\Definition
 */
final class ScriptsMetricDefinition extends NumericMetricDefinition
{
    /**
     * @inheritdoc
     */
    public function createMetric(): Metric
    {
        return new IntegerMetric($this->getName(), 'scripts|amount');
    }
}