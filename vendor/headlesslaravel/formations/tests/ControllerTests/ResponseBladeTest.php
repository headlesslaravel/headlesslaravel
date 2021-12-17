<?php

namespace HeadlessLaravel\Formations\Tests\ControllerTests;

use HeadlessLaravel\Formations\Tests\Fixtures\Models\Post;
use HeadlessLaravel\Formations\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\View\View;

class ResponseBladeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('formations.mode', 'blade');
    }

    public function test_index_blade_responses()
    {
        $this->startSession();

        Post::factory()->create(['title' => 'Hello World']);

        $index = $this->getResourceController()
            ->response('index', Post::query()->paginate());

        $this->assertInstanceOf(View::class, $index);
        $this->assertEquals('testing::posts.index', $index->name());
        $this->assertArrayHasKey('posts', $index->getData());
        $this->assertInstanceOf(LengthAwarePaginator::class, $index->getData()['posts']);
        $this->assertEquals('Hello World', $index->getData()['posts']->first()->title);
        $this->assertEmpty(session()->get('flash'));

        $this->flushSession();
    }

    public function test_create_blade_responses()
    {
        $this->startSession();

        $create = $this
            ->getResourceController()
            ->response('create');

        $this->assertInstanceOf(View::class, $create);
        $this->assertEquals('testing::posts.create', $create->name());
        $this->assertEquals('populated from extra method', $create->getData()['extra']);
        $this->assertEmpty(session()->get('flash'));

        $this->flushSession();
    }

    public function test_show_blade_responses()
    {
        $this->startSession();

        $post = Post::factory()->create(['title' => 'Hello World']);

        $show = $this
            ->getResourceController()
            ->response('show', $post);

        $this->assertInstanceOf(View::class, $show);
        $this->assertEquals('testing::posts.show', $show->name());
        $this->assertArrayHasKey('post', $show->getData());
        $this->assertEquals('Hello World', $show->getData()['post']->title);
        $this->assertEmpty(session()->get('flash'));

        $this->flushSession();
    }

    public function test_store_blade_responses()
    {
        $this->startSession();

        $post = Post::factory()->create(['title' => 'Hello World']);

        $store = $this
            ->getResourceController()
            ->response('store', $post);

        $this->assertInstanceOf(RedirectResponse::class, $store);
        $this->assertEquals(url(route('posts.show', $post)), $store->getTargetUrl());
        $this->assertEquals('store', session()->get('flash.type'));
        $this->assertEquals('Created: Hello World', session()->get('flash.message'));

        $this->flushSession();
    }

    public function test_formation_mode_api_header_skips_redirect()
    {
        $this->authUser();

        config()->set('formations.mode', 'blade');

        $this->withHeader('Wants-Json', true);

       $response = $this->post('posts/new', [
            'title' => 'Blog title',
        ]);

       $response->assertCreated();
       $response->assertJsonPath('post.title', 'Blog title');
    }

    public function test_edit_blade_responses()
    {
        $this->startSession();

        $post = Post::factory()->create(['title' => 'Hello World']);

        $edit = $this
            ->getResourceController()
            ->response('edit', $post);

        $this->assertInstanceOf(View::class, $edit);
        $this->assertEquals('testing::posts.edit', $edit->name());
        $this->assertArrayHasKey('id', $edit->getData());
        $this->assertEquals($post->id, $edit->getData()['id']);
        $this->assertEquals('populated from override method', $edit->getData()['override']);
        $this->assertEmpty(session()->get('flash'));

        $this->flushSession();
    }

    public function test_update_blade_responses()
    {
        $this->startSession();

        $post = Post::factory()->create(['title' => 'Hello World']);

        $update = $this
            ->getResourceController()
            ->response('update', $post);

        $this->assertInstanceOf(RedirectResponse::class, $update);
        $this->assertEquals(url(route('posts.show', $post)), $update->getTargetUrl());
        $this->assertEquals('update', session()->get('flash.type'));
        $this->assertEquals('Updated: Hello World', session()->get('flash.message'));

        $this->flushSession();
    }

    public function test_destroy_blade_responses()
    {
        $this->startSession();

        $post = Post::factory()->create(['title' => 'Hello World']);

        $destroy = $this
            ->getResourceController()
            ->response('destroy', $post);

        $this->assertInstanceOf(RedirectResponse::class, $destroy);
        $this->assertEquals(url(route('posts.index')), $destroy->getTargetUrl());
        $this->assertEquals('destroy', session()->get('flash.type'));
        $this->assertEquals('Deleted: Hello World', session()->get('flash.message'));

        $this->flushSession();
    }

    public function test_restore_blade_responses()
    {
        $this->startSession();

        $post = Post::factory()->create(['title' => 'Hello World']);

        $restore = $this
            ->getResourceController()
            ->response('restore', $post);

        $this->assertInstanceOf(RedirectResponse::class, $restore);
        $this->assertEquals(url(route('posts.show', $post)), $restore->getTargetUrl());
        $this->assertEquals('restore', session()->get('flash.type'));
        $this->assertEquals('Restored: Hello World', session()->get('flash.message'));

        $this->flushSession();
    }

    public function test_force_delete_blade_responses()
    {
        $this->startSession();

        $post = Post::factory()->create(['title' => 'Hello World']);

        $forceDelete = $this
            ->getResourceController()
            ->response('force-delete', $post);

        $this->assertInstanceOf(RedirectResponse::class, $forceDelete);
        $this->assertEquals(url(route('posts.index')), $forceDelete->getTargetUrl());
        $this->assertEquals('force-delete', session()->get('flash.type'));
        $this->assertEquals('Permanently Deleted: Hello World', session()->get('flash.message'));

        $this->flushSession();
    }
}
