<?php

namespace HeadlessLaravel\Metrics\Concerns;

use Illuminate\Support\Carbon;

trait From
{
    public function from(Carbon $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function fromYear(): self
    {
        return $this->from(Carbon::now()->startOfYear());
    }

    public function fromQuarter(): self
    {
        return $this->from(Carbon::now()->startOfQuarter());
    }

    public function fromMonth(): self
    {
        return $this->from(Carbon::now()->startOfMonth());
    }

    public function fromWeek(): self
    {
        return $this->from(Carbon::today()->startOfWeek());
    }

    public function fromDay(): self
    {
        return $this->from(Carbon::now()->startOfDay());
    }

    public function fromHour(): self
    {
        return $this->from(Carbon::now()->startOfHour());
    }

    public function fromMinute(): self
    {
        return $this->from(Carbon::now()->startOfMinute());
    }
}
