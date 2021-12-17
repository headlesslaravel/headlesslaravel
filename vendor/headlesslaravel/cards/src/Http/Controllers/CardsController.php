<?php

namespace HeadlessLaravel\Cards\Http\Controllers;

use HeadlessLaravel\Cards\Manager;

class CardsController
{
    public function __construct(
        public Manager $manager
    ) {
    }

    public function index()
    {
        $group = $this->manager->current();

        return $group->response();
    }

    public function show($key)
    {
        $group = $this->manager->current();

        return $group->response($key);
    }
}
