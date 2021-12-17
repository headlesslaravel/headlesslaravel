<?php

namespace HeadlessLaravel\Formations\Tests\ControllerTests;

use HeadlessLaravel\Formations\Tests\Fixtures\Models\Post;
use HeadlessLaravel\Formations\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authUser();
    }

    public function test_policy_for_indexing_a_resource()
    {
        $this->updateAbilities([]);
        $this->get('posts')->assertForbidden();
        $this->updateAbilities(['viewAny']);
        $this->get('posts')->assertOk();
    }

    public function test_policy_for_creating_a_resource()
    {
        $this->updateAbilities([]);
        $this->get('posts/new')->assertForbidden();
        $this->updateAbilities(['create']);
        $this->get('posts/new')->assertOk();
    }

    public function test_policy_for_storing_a_resource()
    {
        $this->updateAbilities([]);
        $this->post('posts/new')->assertForbidden();
        $this->updateAbilities(['create']);
        $this->post('posts/new', [
            'title' => 'Blog title'
        ])->assertRedirect();
    }

    public function test_policy_for_showing_a_resource()
    {
        $post = Post::factory()->create();
        $this->updateAbilities([]);
        $this->get("posts/$post->id")->assertForbidden();
        $this->updateAbilities(['view']);
        $this->get("posts/$post->id")->assertOk();
    }

    public function test_policy_for_editing_a_resource()
    {
        $post = Post::factory()->create();
        $this->updateAbilities([]);
        $this->get("posts/$post->id/edit")->assertForbidden();
        $this->updateAbilities(['update']);
        $this->get("posts/$post->id/edit")->assertOk();
    }

    public function test_policy_for_updating_a_resource()
    {
        $post = Post::factory()->create();
        $this->updateAbilities([]);
        $this->put("posts/$post->id/edit")->assertForbidden();
        $this->updateAbilities(['update']);
        $this->put("posts/$post->id/edit", [
            'title' => 'new title'
        ])->assertRedirect();
    }

    public function test_policy_for_deleting_a_resource()
    {
        $post = Post::factory()->create();
        $this->updateAbilities([]);
        $this->delete("posts/$post->id")->assertForbidden();
        $this->updateAbilities(['delete']);
        $this->delete("posts/$post->id")->assertRedirect();
    }

    public function test_policy_for_restoring_a_resource()
    {
        $post = Post::factory()->create();
        $this->updateAbilities([]);
        $this->put("posts/$post->id/restore")->assertForbidden();
        $this->updateAbilities(['restore',]);
        $this->put("posts/$post->id/restore")->assertRedirect();
    }

    public function test_policy_for_force_deleting_a_resource()
    {
        $post = Post::factory()->create();
        $this->updateAbilities([]);
        $this->delete("posts/$post->id/force-delete")->assertForbidden();
        $this->updateAbilities(['forceDelete']);
        $this->delete("posts/$post->id/force-delete")->assertRedirect();
    }
}
