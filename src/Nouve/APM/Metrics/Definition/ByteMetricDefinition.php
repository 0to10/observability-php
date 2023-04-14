<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics\Definition;

use Nouve\APM\Metrics\IntegerMetric;
use Nouve\APM\Metrics\Metric;

/**
 * BytesMetricDefinition
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics\Definition
 */
final class ByteMetricDefinition extends NumericMetricDefinition
{
    /**
     * @inheritdoc
     */
    public function createMetric(): Metric
    {
        return new IntegerMetric($this->label, 'size|bytes');
    }
}
