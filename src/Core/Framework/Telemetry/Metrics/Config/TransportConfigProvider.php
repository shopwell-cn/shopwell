<?php declare(strict_types=1);

namespace Shopwell\Core\Framework\Telemetry\Metrics\Config;

use Shopwell\Core\Framework\Log\Package;
use Shopwell\Core\Framework\Telemetry\Metrics\Metric\Type;

/**
 * @internal
 *
 * @phpstan-import-type MetricDefinition from MetricConfig
 */
#[Package('framework')]
class TransportConfigProvider
{
    public function __construct(private readonly MetricConfigProvider $metricConfigProvider)
    {
    }

    public function getTransportConfig(): TransportConfig
    {
        return new TransportConfig(metricsConfig: $this->metricConfigProvider->all());
    }
}
