<?php

namespace HeadlessLaravel\Formations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use HeadlessLaravel\Formations\Manager;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return app(Manager::class)
            ->formation()
            ->rulesForUpdating();
    }
}
