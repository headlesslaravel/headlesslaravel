<?php

namespace HeadlessLaravel\Metrics;

trait HasMetrics
{
    public static function metrics(): Metric
    {
        return Metric::make(static::class);
    }
}
