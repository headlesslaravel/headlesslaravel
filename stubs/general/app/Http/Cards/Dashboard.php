<?php

namespace App\Http\Cards;

use App\Models\User;
use HeadlessLaravel\Cards\Card;
use HeadlessLaravel\Cards\Cards;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Cards
{
    public function rules(): array
    {
        return [
            //
        ];
    }

    public function cards(): array
    {
        return [
            Card::make('Welcome')
                ->span(4)
                ->value(function () {
                    return 'Welcome back, '.Auth::user()->name;
                }),

            Card::make('Environment')
                ->span(5)
                ->value(function () {
                    return App::environment();
                }),

            Card::make('Total Users')
                ->span(3)
                ->value(function () {
                    return User::count();
                }),

            Card::make('Artisan Inspire')
                ->span(6)
                ->value(function () {
                    return Inspiring::quote();
                }),

            Card::make('Today\'s Date')
                ->span(3)
                ->value(function () {
                    return Carbon::today()->format('m/d/Y');
                }),

            Card::make('Current Time')
                ->span(3)
                ->value(function () {
                    return Carbon::now()->format('h:ma');
                }),
        ];
    }
}
