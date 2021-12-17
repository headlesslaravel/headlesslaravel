<?php

namespace HeadlessLaravel\Formations\Tests\Fixtures;

use HeadlessLaravel\Formations\Filter;
use HeadlessLaravel\Formations\Formation;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\Post;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\Tag;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\User;

class TagFormation extends Formation
{
    public $search = ['title'];

    public $model = Tag::class;
}
