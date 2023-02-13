<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics;

/**
 * FloatMetric
 *
 * @author Ted Vossen <ted@nouve.nl>
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics
 */
final class FloatMetric extends Metric
{
    /**
     * @inheritdoc
     */
    protected function finalizeValue($value): float
    {
        return (float)$value;
    }
}