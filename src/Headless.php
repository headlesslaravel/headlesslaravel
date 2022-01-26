<?php

namespace HeadlessLaravel;

use Illuminate\Support\Facades\Facade;

class Headless extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
