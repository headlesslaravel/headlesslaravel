<?php

namespace HeadlessLaravel\Formations\Tests\ControllerTests;

use HeadlessLaravel\Formations\Exceptions\UnregisteredFormation;
use HeadlessLaravel\Formations\Manager;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\Post;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\User;
use HeadlessLaravel\Formations\Tests\Fixtures\PostFormation;
use HeadlessLaravel\Formations\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

class NestedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authUser();

        config()->set('formations.mode', 'api');
    }

    public function test_unregistered_formation_exception()
    {
        $this->expectException(UnregisteredFormation::class);

        Route::formation('users.posts', PostFormation::class);
    }

    public function test_indexing_a_nested_resource()
    {
        Post::factory()->create();

        $post = Post::factory()->create();

        $this->get("authors/$post->author_id/posts")
            ->assertOk()
            ->assertJsonCount(1, 'posts')
            ->assertJsonPath('posts.0.id', $post->id);
    }

    public function test_index_with_no_nested_resources()
    {
        Post::factory()->create();

        $author = User::factory()->create();

        $this->get("authors/$author->id/posts")
            ->assertOk()
            ->assertJsonPath('data', null)
            ->assertJsonCount(0, 'posts');
    }

    public function test_searching_a_nested_resource_index()
    {
        Post::factory()->create();

        $post = Post::factory()->create(['title' => 'Find me']);

        $this->get("authors/$post->author_id/posts?search=find")
            ->assertOk()
            ->assertJsonCount(1, 'posts')
            ->assertJsonPath('posts.0.id', $post->id);
    }

    public function test_creating_a_nested_resource_returns_parent()
    {
        $post = Post::factory()->create();

        $this->get("authors/$post->author_id/posts/new")
            ->assertOk()
            ->assertJsonPath('author.id', $post->author->id)
            ->assertJsonPath('author.name', $post->author->name);
    }

    public function test_storing_a_nested_resource()
    {
        $author = User::factory()->create();

        $response = $this->post("authors/$author->id/posts/new", [
            'title' => 'Blog title',
        ]);

        $response->assertJsonPath('author.id', $author->id);
        $this->assertEquals('Blog title', Post::first()->title);
        $this->assertEquals($author->id, Post::first()->author->id);
    }

    public function test_showing_a_nested_resource()
    {
        $post = Post::factory()->create();

        $this->get("authors/$post->author_id/posts/$post->id")
            ->assertOk()
            ->assertJsonPath('post.id', $post->id)
            ->assertJsonPath('author.id', $post->author->id);
    }

    public function test_showing_a_unrelated_nested_resource()
    {
        $unrelated = User::factory()->create();

        $post = Post::factory()->create();

        $this->get("authors/$unrelated->id/posts/$post->id")->assertNotFound();
    }

    public function test_showing_a_deleted_nested_resource()
    {
        $post = Post::factory()->create();

        $post->delete();

        $this->get("authors/$post->author_id/posts/$post->id")
            ->assertOk()
            ->assertJsonPath('post.id', $post->id)
            ->assertJsonPath('author.id', $post->author->id);
    }

    public function test_editing_a_nested_resource()
    {
        $post = Post::factory()->create();

        $this->get("authors/$post->author_id/posts/$post->id/edit")
            ->assertOk()
            ->assertJsonPath('post.id', $post->id)
            ->assertJsonPath('author.id', $post->author->id);
    }

    public function test_updating_a_nested_resource()
    {
        $post = Post::factory()->create();

        $this->put("authors/$post->author_id/posts/$post->id/edit", [
            'title' => 'new title goes here',
        ])->assertOk();

        $this->assertEquals(
            'new title goes here',
            $post->fresh()->title
        );
    }

    public function test_deleting_a_nested_resource()
    {
        $post = Post::factory()->create();

        $this->delete("authors/$post->author_id/posts/$post->id")->assertOk();

        $this->assertCount(0, Post::all());
        $this->assertCount(1, Post::withTrashed()->get());
    }

    public function test_restoring_a_nested_resource()
    {
        $post = Post::factory()->create();

        $post->delete();

        $this->put("authors/$post->author_id/posts/$post->id/restore")->assertOk();

        $this->assertCount(1, Post::all());
    }

    public function test_force_deleting_a_nested_resource()
    {
        $post = Post::factory()->create();

        $post->delete();

        $this->delete("authors/$post->author_id/posts/$post->id/force-delete")->assertOk();

        $this->assertEquals(0, Post::withTrashed()->count());
    }
}
