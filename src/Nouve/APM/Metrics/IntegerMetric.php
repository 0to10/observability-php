<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics;

/**
 * IntegerMetric
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics
 */
final class IntegerMetric extends Metric
{
    /**
     * @inheritdoc
     */
    protected function finalizeValue($value): int
    {
        return (int)$value;
    }
}