<?php

namespace HeadlessLaravel\Metrics\Concerns;

trait Intervals
{
    public function by(string $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function byMinute(): self
    {
        return $this->by('minute');
    }

    public function byHour(): self
    {
        return $this->by('hour');
    }

    public function byDay(): self
    {
        return $this->by('day');
    }

    // TODO: https://stackoverflow.com/a/1736018/1342440

    public function byWeek(): self
    {
        return $this->by('week');
    }

    public function byMonth(): self
    {
        return $this->by('month');
    }

    // TODO: https://stackoverflow.com/a/6067653/1342440

    public function byQuarter(): self
    {
        return $this->by('quarter');
    }

    public function byYear(): self
    {
        return $this->by('year');
    }
}
