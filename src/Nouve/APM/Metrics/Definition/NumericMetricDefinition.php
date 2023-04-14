<?php
declare(strict_types=1);
namespace Nouve\APM\Metrics\Definition;

/**
 * NumericMetricDefinition
 *
 * @copyright Copyright (c) 2018 Nouvé B.V. <https://nouve.nl>
 * @package Nouve\APM\Metrics\Definition
 */
abstract class NumericMetricDefinition extends MetricDefinition
{
    /**
     * @param string $label
     * @return static
     */
    public function label(string $label): MetricDefinition
    {
        $this->label = $label;
        return $this;
    }
}
