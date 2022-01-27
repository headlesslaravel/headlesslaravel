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
    public $search = ['id', 'name', 'email'];

    /**
     * The sortable columns.
     *
     * @var array
     */
    public $sort = ['id', 'name', 'email', 'created_at'];

    /**
     * Define the index fields.
     *
     * @return array
     */
    public function index(): array
    {
        return [
            Field::make('id'),
            Field::make('name'),
            Field::make('email'),
            Field::make('created_at'),
        ];
    }

    /**
     * Define the form fields.
     *
     * @return array
     */
    public function form(): array
    {
        return [
            Field::make('name')->rules('required|min:5'),
            Field::make('email')->rules('required|email'),
            Field::make('password')->rules('required|min:8'),
        ];
    }

    /**
     * Define the filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            Filter::make('trash')->trash(),
        ];
    }
}
