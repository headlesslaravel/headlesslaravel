<?php

namespace HeadlessLaravel\Formations\Tests\ControllerTests;

use HeadlessLaravel\Formations\Tests\Fixtures\Models\Post;
use HeadlessLaravel\Formations\Tests\Fixtures\PostFormation;
use HeadlessLaravel\Formations\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

class ResourceValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_storing_a_resource()
    {
        $this->authUser();

        $this->post('/posts/new', [
            'title' => null
        ])->assertInvalid(['title' => 'The title field is required.']);
    }

    public function test_validate_updating_a_resource()
    {
        $this->authUser();

        $post = Post::factory()->create();

        $this->put("/posts/$post->id/edit", [
            'title' => 'only 6'
        ])->assertInvalid(['title' => 'The title must be at least 10 characters.']);
    }
}
