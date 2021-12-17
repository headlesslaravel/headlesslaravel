<?php

namespace HeadlessLaravel\Formations\Tests\ControllerTests;

use HeadlessLaravel\Formations\Http\Controllers\Controller;
use HeadlessLaravel\Formations\Http\Resources\Resource;
use HeadlessLaravel\Formations\Manager;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\Post;
use HeadlessLaravel\Formations\Tests\Fixtures\PostFormation;
use HeadlessLaravel\Formations\Tests\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ControllerTest extends TestCase
{
    public function test_resource_singleton()
    {
        $resources = app(Manager::class)->all();

        $this->assertEquals('posts', $resources[0]['resource']);
        $this->assertEquals(PostFormation::class, $resources[0]['formation']);
    }

    public function test_resource_terms()
    {
        $controller = app(Controller::class);
        $controller->current['resource'] = 'product-lines';

        $this->assertEquals('ProductLine', $controller->terms('resource.studly'));
        $this->assertEquals('ProductLines', $controller->terms('resource.studlyPlural'));
        $this->assertEquals('product_line', $controller->terms('resource.snake'));
        $this->assertEquals('product_lines', $controller->terms('resource.snakePlural'));
        $this->assertEquals('product-line', $controller->terms('resource.slug'));
        $this->assertEquals('product-lines', $controller->terms('resource.slugPlural'));
        $this->assertEquals('productLine', $controller->terms('resource.camel'));
        $this->assertEquals('productLines', $controller->terms('resource.camelPlural'));
    }
}
