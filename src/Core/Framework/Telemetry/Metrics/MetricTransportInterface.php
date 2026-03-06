<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Telemetry\Metrics;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Telemetry\Metrics\Exception\MetricNotSupportedException;
use Shopwell\Core\Framework\Telemetry\Metrics\Metric\Metric;

/**
 * @experimental feature:TELEMETRY_METRICS stableVersion:v6.8.0
 */
#[Package('framework')]
interface MetricTransportInterface
{
    /**
     * @throws MetricNotSupportedException
     */
    public function emit(Metric $metric): void;
}
