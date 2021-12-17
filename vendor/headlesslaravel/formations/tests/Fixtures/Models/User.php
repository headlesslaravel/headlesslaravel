<?php

namespace HeadlessLaravel\Formations\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Orchestra\Testbench\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory;

    public $guarded = [];

    public static function newFactory()
    {
        return UserFactory::new();
    }
}
