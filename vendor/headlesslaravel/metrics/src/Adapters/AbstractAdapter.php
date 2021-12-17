<?php

namespace HeadlessLaravel\Metrics\Adapters;

abstract class AbstractAdapter
{
    abstract public function format(string $column, string $interval): string;
}
