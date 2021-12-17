<?php

namespace HeadlessLaravel\Metrics;

class MetricResult
{
    public function __construct(
        public string $date,
        public mixed $aggregate,
    ) {
    }
}
