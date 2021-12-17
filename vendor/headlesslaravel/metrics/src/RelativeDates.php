<?php

namespace HeadlessLaravel\Metrics;

use Carbon\CarbonImmutable;

class RelativeDates
{
    public static function make()
    {
        $today = CarbonImmutable::today();

        return [
            'all' => [
                'display' => 'All Time',
                'from' => '',
                'to' => ''
            ],
            'day' => [
                'display' => 'Today',
                'from' => $today,
                'to' => $today->endOfDay()
            ],
            'sub_day' => [
                'display' => 'Yesterday',
                'from' => $today->subDay(),
                'to' => $today->subDay()->endOfDay()
            ],
            'week' => [
                'display' => 'This Week',
                'from' => $today->startOfWeek(),
                'to' => $today->endOfWeek()
            ],
            'sub_week' => [
                'display' => 'Last Week',
                'from' => $today->subWeek()->startOfWeek(),
                'to' => $today->subWeek()->endOfWeek()
            ],
            'month' => [
                'display' => 'This Month',
                'from' => $today->startOfMonth(),
                'to' => $today->endOfMonth()
            ],
            'sub_month' => [
                'display' => 'Last Month',
                'from' => $today->subMonth()->startOfMonth(),
                'to' => $today->subMonth()->endOfMonth()
            ],
            'quarter' => [
                'display' => 'This Quarter',
                'from' => $today->startOfQuarter(),
                'to' => $today->endOfQuarter()
            ],
            'sub_quarter' => [
                'display' => 'Last Quarter',
                'from' => $today->subQuarter()->startOfQuarter(),
                'to' => $today->subQuarter()->endOfQuarter()
            ],
            'year' => [
                'display' => 'This Year',
                'from' => $today->startOfYear(),
                'to' => $today->endOfYear()
            ],
            'sub_year' => [
                'display' => 'Last Year',
                'from' => $today->subYear()->startOfYear(),
                'to' => $today->subYear()->endOfYear()
            ],
        ];
    }
}
