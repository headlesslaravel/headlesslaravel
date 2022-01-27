<?php

namespace App\Http\Formations;

use HeadlessLaravel\Formations\Field;
use HeadlessLaravel\Formations\Filter;
use HeadlessLaravel\Formations\Formation;

class UserFormation extends Formation
{
    /**
     * The model class.
     *
     * @var string
     */
    public $model = \App\Models\User::class;

    /**
     * The display column for options.
     *
     * @var string
     */
    public $display = 'name';

    /**
     * The searchable columns.
     *
     * @var array
     */
    public $search = ['name', 'email'];

    /**
     * The sortable columns.
     *
     * @var array
     */
    public $sort = ['name', 'email', 'created_at'];

    /**
     * Define the fields.
     *
     * @return array
     */
    public function fields():array
    {
        return [
            Field::make('id'),
            Field::make('name')->rules('required'),
            Field::make('email')->rules('required'),
            Field::make('password')->rules('required'),
            Field::make('created_at'),
        ];
    }

    /**
     * Define the filters.
     *
     * @return array
     */
    public function filters():array
    {
        return [
            //
            Filter::make('trash')->trash(),
        ];
    }
}
