<?php

namespace HeadlessLaravel\Formations\Tests\FilterTests;

use HeadlessLaravel\Formations\Exceptions\ReservedException;
use HeadlessLaravel\Formations\Exceptions\UnauthorizedException;
use HeadlessLaravel\Formations\Filter;
use HeadlessLaravel\Formations\Formation;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\Comment;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\Like;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\Post;
use HeadlessLaravel\Formations\Tests\Fixtures\Models\Tag;
use HeadlessLaravel\Formations\Tests\Fixtures\PostFormation;
use HeadlessLaravel\Formations\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class GeneralTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Route::get('/posts', function (PostFormation $formation) {
            $formation->validate();
            return $formation->results();
        });
    }

    public function test_no_parameters()
    {
        Post::factory(3)->create();

        $this->get('/posts')
            ->assertJsonCount(3, 'data');
    }

    public function test_search()
    {
        Post::factory(3)->create();

        $expected = Post::create(['title' => 'hello world']);

        $this->get('/posts?search=world')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_search_relations()
    {
        $rejected = Post::factory()->create();
        $expected = Post::factory()->create();

        Comment::factory()->create([
            'body' => 'Laravel is an amazing framework',
            'post_id' => $expected->id,
        ]);

        $this->get('/posts?search=amazing')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_search_relations_with_pivots()
    {
        $tag1 = Tag::create(['title' => 'PHP']);
        $tag2 = Tag::create(['title' => 'JS']);

        $rejected = Post::factory()->create();
        $expected = Post::factory()->create();

        $rejected->tags()->attach([$tag2->id]);
        $expected->tags()->attach([$tag1->id]);

        $this->get('/posts?search=php')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_per_page()
    {
        Post::factory(4)->create();

        $this->get('/posts?per_page=2')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 4);
    }

    public function test_max_per_page()
    {
        $this->get('/posts?per_page=200')
            ->assertJsonPath('per_page', 100);
    }

    public function test_redirect_page_if_exceeds_total_pages()
    {
        $this->get('/posts?page=2&search=hello')
            ->assertRedirect('/posts?page=1&search=hello&sort-desc=body');
            // sort comes from $defaults
    }

    public function test_sorting_invalid_key()
    {
        $this->get('/posts?sort-desc=invalid')
            ->assertSessionHasErrors();
    }

    public function test_sorting_columns()
    {
        Post::create(['title' => 3]);
        Post::create(['title' => 1]);
        Post::create(['title' => 2]);

        $this->get('/posts?sort-desc=title')
            ->assertJsonPath('data.0.title', '3')
            ->assertJsonPath('data.1.title', '2')
            ->assertJsonPath('data.2.title', '1');

        $this->get('/posts?sort=title')
            ->assertJsonPath('data.0.title', '1')
            ->assertJsonPath('data.1.title', '2')
            ->assertJsonPath('data.2.title', '3');
    }

    public function test_sorting_relationships()
    {
        $threeComments = Post::factory()->has(
            Comment::factory()->count(3)
        )->create();

        $oneComment = Post::factory()->has(
            Comment::factory()->count(1)
        )->create();

        $twoComments = Post::factory()->has(
            Comment::factory()->count(2)
        )->create();

        $this->get('/posts?sort-desc=comments')
            ->assertJsonPath('data.0.id', $threeComments->id)
            ->assertJsonPath('data.1.id', $twoComments->id)
            ->assertJsonPath('data.2.id', $oneComment->id);

        $this->get('/posts?sort=comments')
            ->assertJsonPath('data.0.id', $oneComment->id)
            ->assertJsonPath('data.1.id', $twoComments->id)
            ->assertJsonPath('data.2.id', $threeComments->id);
    }

    public function test_sorting_relationship_columns()
    {
        $twoUpvote = Post::factory()->create();
        $oneUpvote = Post::factory()->create();
        $sixUpvote = Post::factory()->create();

        $twoUpvote->comments()->create(['upvotes' => 2]);
        $oneUpvote->comments()->create(['upvotes' => 1]);
        $sixUpvote->comments()->create(['upvotes' => 6]);

        $this->get('/posts?sort-desc=upvotes')
            ->assertJsonPath('data.0.id', $sixUpvote->id)
            ->assertJsonPath('data.1.id', $twoUpvote->id)
            ->assertJsonPath('data.2.id', $oneUpvote->id);

        $this->get('/posts?sort=upvotes')
            ->assertJsonPath('data.0.id', $oneUpvote->id)
            ->assertJsonPath('data.1.id', $twoUpvote->id)
            ->assertJsonPath('data.2.id', $sixUpvote->id);
    }

    public function test_sorting_relationship_column_with_alias()
    {
        $twoDownvote = Post::factory()->create();
        $oneDownvote = Post::factory()->create();
        $sixDownvote = Post::factory()->create();

        $twoDownvote->comments()->create(['downvotes' => 2]);
        $oneDownvote->comments()->create(['downvotes' => 1]);
        $sixDownvote->comments()->create(['downvotes' => 6]);

        $this->get('/posts?sort-desc=disliked')
            ->assertJsonPath('data.0.id', $sixDownvote->id)
            ->assertJsonPath('data.1.id', $twoDownvote->id)
            ->assertJsonPath('data.2.id', $oneDownvote->id);
    }

    public function test_filtering_default()
    {
        Post::create();

        $post = Post::create();

        $this->get('/posts?id='.$post->id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post->id);
    }

    public function test_filtering_multiple_default()
    {
        Post::factory()->create();

        $one = Post::factory()->create();
        $two = Post::factory()->create();

        $this->get('/posts?author_id='.$one->author_id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $one->id);

        $this->get('/posts?author_id[]='.$one->author_id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $one->id);

        $this->get('/posts?author_id[]='.$one->author_id.'&author_id[]='.$two->author_id)
            ->assertJsonCount(2, 'data');
    }

    public function test_filtering_booleans()
    {
        Post::create();

        $post = Post::create(['active' => true]);

        $this->get('/posts?active=true')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post->id);
    }

    public function test_filtering_toggles()
    {
        Post::create();

        $post = Post::create(['active' => true]);

        $this->get('/posts?toggle=false')->assertSessionHasErrors();

        $this->get('/posts?toggle=true')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post->id);
    }

    public function test_filtering_dates()
    {
        Post::factory(3)->create();

        $expected = Post::create(['published_at' => '2021-01-01 00:00:00']);

        $this->get('/posts?published_at=01/01/2021')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);

        $this->get('/posts?published_at[]=01/01/2021')
            ->assertSessionHasErrors();
    }

    public function test_filtering_multiple_dates()
    {
        Post::factory(3)->create();
        Post::create(['published_at' => '2020-01-01 00:00:00']);
        Post::create(['published_at' => '2021-01-01 00:00:00']);

        $this->get('/posts?multiple_dates=01/01/2020')
            ->assertJsonCount(1, 'data');

        $this->get('/posts?multiple_dates[]=01/01/2020')
            ->assertJsonCount(1, 'data');

        $this->get('/posts?multiple_dates[]=01/01/2020&multiple_dates[]=01/01/2021')
            ->assertJsonCount(2, 'data');
    }

    public function test_filtering_date_range_min()
    {
        Post::create(['created_at' => '2020-01-01 00:00:00']);

        $expected = Post::create();

        $this->get('/posts?created_at:min=01-01-2021')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_date_range_max()
    {
        Post::create(['created_at' => '2021-01-01 00:00:00']);

        $expected = Post::create(['created_at' => '2020-01-01 00:00:00']);

        $this->get('/posts?created_at:max=01-01-2020')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_date_range_min_max()
    {
        $expected = Post::factory()->has(
            Comment::factory()->count(2)
        )->create();

        $rejected = Post::factory()->has(
            Comment::factory()->count(4)
        )->create();

        $this->get('/posts?comments:min=2&comments:max=3')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_select_options()
    {
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'inactive']);

        $this->get('posts?status=wrong')->assertSessionHasErrors();
        $this->get('posts?status=active')->assertJsonCount(2, 'data');
        $this->get('posts?status=inactive')->assertJsonCount(1, 'data');
        $this->get('posts?status[]=inactive&status[]=active')->assertSessionHasErrors();
    }

    public function test_filtering_multiple_select_options()
    {
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'inactive']);

        $this->get('posts?multiple=wrong')->assertSessionHasErrors([
            'multiple' => 'The selected multiple is invalid.',
        ]);

        $this->get('posts?multiple=active')->assertJsonCount(2, 'data');
        $this->get('posts?multiple=inactive')->assertJsonCount(1, 'data');
        $this->get('posts?multiple[]=inactive&multiple[]=active')->assertJsonCount(3, 'data');
    }

    public function test_filtering_range_min()
    {
        $expected = Post::create(['length' => 5]);

        Post::create(['length' => 1]);

        $this->get('/posts?length:min=5')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_range_max()
    {
        $expected = Post::create(['length' => 5]);

        Post::create(['length' => 10]);

        $this->get('/posts?length:max=5')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_range_between()
    {
        $expected = Post::create(['length' => 5]);

        Post::create(['length' => 11]);
        Post::create(['length' => 4]);

        $this->get('/posts?length:min=5&length:max=10')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_range_with_same_min_max()
    {
        Post::create(['length' => 11]);

        $expected = Post::create(['length' => 5]);

        $this->get('/posts?length:min=5&length:max=5')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_range_must_be_logical()
    {
        $this->get('/posts?length:min=5&length:max=1')
            ->assertSessionHasErrors([
                'length:min' => 'Must be less than max.',
                'length:max' => 'Must be greater than min.',
            ]);
    }

    public function test_filtering_range_has_one_but_other_is_empty()
    {
        Post::create(['length' => 11]);

        $this->get('/posts?length:min=1&length:max=')
            ->assertJsonCount(1, 'data');

        $this->get('/posts?length:min=&length:max=11')
            ->assertJsonCount(1, 'data');
    }

    public function test_filtering_between_sets()
    {
        $small = Post::create(['length' => 9]);
        $medium = Post::create(['length' => 19]);
        $large = Post::create(['length' => 29]);

        $this->get('/posts?length-range=small')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $small->id);

        $this->get('/posts?length-range=medium')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $medium->id);

        $this->get('/posts?length-range=large')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $large->id);
    }

    public function test_filtering_scopes()
    {
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'inactive']);

        $this->get('posts?active-scope=true')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'active')
            ->assertJsonPath('data.1.status', 'active');

        $this->get('posts?active-scope=false')
            ->assertJsonCount(2, 'data');
    }

    public function test_filtering_scopes_by_value()
    {
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'inactive']);

        $this->get('posts?value-scope=active')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'active')
            ->assertJsonPath('data.1.status', 'active');

        $this->get('posts?value-scope=inactive')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'inactive');
    }

    public function test_filtering_scope_boolean()
    {
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'active']);
        Post::factory()->create(['status' => 'inactive']);

        $this->get('posts?boolean-scope=false')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'inactive');

        $this->get('posts?boolean-scope=true')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', 'active')
            ->assertJsonPath('data.1.status', 'active');
    }

    public function test_filtering_soft_deletes()
    {
        Post::factory()->create();
        Post::factory()->create();
        $deleted = Post::factory()->create();

        $deleted->delete();

        $this->get('posts')
            ->assertJsonCount(2, 'data');

        $this->get('posts?trashed=true')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $deleted->id);

        $this->get('posts?with-trashed=true')
            ->assertJsonCount(3, 'data');
    }

    public function test_filtering_related()
    {
        $one = Post::factory()->create();
        $two = Post::factory()->create();

        $this->get('posts?author='.$one->author_id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $one->id);

        $this->get('posts?author='.$two->author_id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $two->id);

        $this->get('posts?author[]=1&author[]=2')
            ->assertSessionHasErrors([
                'author' => 'Multiple not permitted.',
            ]);
    }

    public function test_filtering_multiple_related()
    {
        $one = Post::factory()->create();
        $two = Post::factory()->create();

        $this->get("posts?writer={$one->author_id}")
            ->assertJsonCount(1, 'data');

        $this->get("posts?writer[]={$one->author_id}&writer[]={$two->author_id}")
            ->assertJsonCount(2, 'data');
    }

    public function test_filtering_relationship_exists()
    {
        $user = $this->authUser();
        $rejected = Post::factory()->create();
        $expected = Post::factory()->create();

        Like::create([
            'post_id' => $expected->id,
            'user_id' => $user->id,
        ]);

        $this->get('/posts?like:exists=true')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_relationship_doesnt_exist()
    {
        $user = $this->authUser();
        $expected = Post::factory()->create();
        $post = Post::factory()->create();

        Like::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->get('/posts?like:exists=false')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_relationship_count()
    {
        $rejected = Post::factory()->has(
            Comment::factory()->count(2)
        )->create();

        $expected = Post::factory()->has(
            Comment::factory()->count(4)
        )->create();

        $this->get('/posts?comments:count=4')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_relationship_count_range_min()
    {
        $rejected = Post::factory()->has(
            Comment::factory()->count(1)
        )->create();

        $expected = Post::factory()->has(
            Comment::factory()->count(2)
        )->create();

        $this->get('/posts?comments:min=2')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_relationship_count_range_max()
    {
        $rejected = Post::factory()->has(
            Comment::factory()->count(4)
        )->create();

        $expected = Post::factory()->has(
            Comment::factory()->count(2)
        )->create();

        $this->get('/posts?comments:max=2')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_relationship_count_range_between()
    {
        Post::factory()->has(
            Comment::factory()->count(1)
        )->create();

        Post::factory()->has(
            Comment::factory()->count(5)
        )->create();

        $expected = Post::factory()->has(
            Comment::factory()->count(3)
        )->create();

        $this->get('/posts?comments:min=2&comments:max=4')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_relationship_count_range_between_with_empty_value()
    {
        Post::factory()->has(
            Comment::factory()->count(1)
        )->create();

        $rejected = Post::factory()->has(
            Comment::factory()->count(5)
        )->create();

        Post::factory()->has(
            Comment::factory()->count(3)
        )->create();

        $this->get('/posts?comments:min=&comments:max=4')
            ->assertJsonCount(2, 'data');
    }

    public function test_filtering_relationship_count_range_must_be_logical()
    {
        $this->get('/posts?comments:min=2&comments:max=1')
            ->assertSessionHasErrors([
                'comments:min' => 'Must be less than max.',
                'comments:max' => 'Must be greater than min.',
            ]);
    }

    public function test_filtering_relationship_with_pivots()
    {
        $tag1 = Tag::create(['title' => 'PHP']);
        $tag2 = Tag::create(['title' => 'JS']);

        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        $post1->tags()->attach([$tag1->id]);
        $post2->tags()->attach([$tag2->id]);

        $this->get('/posts?tagged='.$tag1->id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post1->id);

        $this->get('/posts?tagged='.$tag2->id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post2->id);
    }

    public function test_filtering_relationship_with_pivots_and_multiple()
    {
        $tag1 = Tag::create(['title' => 'PHP']);
        $tag2 = Tag::create(['title' => 'JS']);

        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        $post1->tags()->attach([$tag1->id, $tag2->id]);
        $post2->tags()->attach([$tag2->id]);

        $this->get('/posts?tagged[]=abc')
            ->assertJsonCount(0, 'data');

        $this->get('/posts?tagged[]='.$tag1->id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $post1->id);

        $this->get('/posts?tagged[]='.$tag1->id.'&tagged[]='.$tag2->id)
            ->assertJsonCount(2, 'data');
    }

    public function test_filtering_relationship_exists_with_pivots()
    {
        $tag1 = Tag::create(['title' => 'PHP']);
        $tag2 = Tag::create(['title' => 'JS']);

        $rejected = Post::factory()->create();
        $expected = Post::factory()->create();

        $expected->tags()->attach([$tag1->id, $tag2->id]);

        $this->get('/posts?tags:exists=true')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_relationship_count_with_pivots()
    {
        $tag1 = Tag::create(['title' => 'PHP']);
        $tag2 = Tag::create(['title' => 'JS']);

        $rejected = Post::factory()->create();
        $expected = Post::factory()->create();

        $rejected->tags()->attach($tag1->id);
        $expected->tags()->attach([$tag1->id, $tag2->id]);

        $this->get('/posts?tags:count=2')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filtering_relationship_count_range_with_pivots()
    {
        $tag1 = Tag::create(['title' => 'PHP']);
        $tag2 = Tag::create(['title' => 'JS']);

        $one = Post::factory()->create();
        $two = Post::factory()->create();

        $one->tags()->attach($tag1->id);
        $two->tags()->attach([$tag1->id, $tag2->id]);

        $this->get('/posts?tags:min=2')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $two->id);

        $this->get('/posts?tags:max=1')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $one->id);
    }

    public function test_filtering_with_search_terms()
    {
        Post::factory()->create();
        $post = Post::factory()->create();
        Post::factory()->create();

        $this->get('/posts?written-by='.$post->author->name)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.author_id', (string) $post->author_id);
    }

    public function test_filtering_auth_required()
    {
        $this->withoutExceptionHandling();
        $this->expectException(UnauthorizedException::class);
        $this->get('/posts?like:exists=true');
    }

    public function test_filtering_auth_exception_renders_redirect()
    {
        Route::get('login')->name('login');
        $redirect = (new UnauthorizedException())->render(request());
        $this->assertTrue($redirect->isRedirect());
    }

    public function test_rules_defined_on_request_class()
    {
        $this->get('/posts?rule_test=invalid-value')
            ->assertSessionHasErrors(['rule_test' => 'The selected rule test is invalid.']);

        session()->flush();

        $this->get('/posts?rule_test=allowed-value')
            ->assertSessionHasNoErrors();
    }

    public function test_rules_override_filter_rules_but_not_request_rules()
    {
        $filter = Filter::make('amount')
            ->options(['1', '2', '3'])
            ->rules('gt:2');

        $rules = $filter->getRules();

        $this->assertEquals(['gt:2'], $rules['amount']);

        $this->get('/posts?rule_test=not-allowed-value')
            ->assertSessionHasErrors(['rule_test' => 'The selected rule test is invalid.']);
    }

    public function test_rule_appending()
    {
        $filter = Filter::make('append-rules')
            ->options(['1', '2', '3'])
            ->withRules('gt:2');

        $rules = $filter->getRules();

        $this->assertEquals(
            ['nullable', 'in:1,2,3', 'gt:2'],
            $rules['append-rules']
        );
    }

    public function test_rule_appending_with_multiple()
    {
        $filter = Filter::make('amount')
            ->options(['1', '2', '3'])
            ->withRules('gt:2')
            ->multiple();

        $filter->setRequest(request()->merge(['amount' => [1, 2, 3]]));

        $rules = $filter->getRules();

        $this->assertEquals(
            ['nullable', 'in:1,2,3', 'gt:2'],
            $rules['amount.*']
        );
    }

    public function test_rules_appending_with_multiple_but_single_value()
    {
        $filter = Filter::make('amount')
            ->options(['1', '2', '3'])
            ->withRules('gt:2')
            ->multiple();

        $filter->setRequest(request()->merge(['amount' => 1]));

        $rules = $filter->getRules();

        $this->assertEquals(
            ['nullable', 'in:1,2,3', 'gt:2'],
            $rules['amount']
        );
    }

    public function test_rules_appending_with_public_key()
    {
        $filter = Filter::make('other-name', 'append-rules')
            ->options(['1', '2', '3'])
            ->withRules('gt:2');

        $rules = $filter->getRules();

        $this->assertFalse(
            isset($rules['append-rules'])
        );

        $this->assertEquals(
            ['nullable', 'in:1,2,3', 'gt:2'],
            $rules['other-name']
        );
    }

    public function test_rules_from_list_request()
    {
        $this->get('/posts?per_page=abc')
            ->assertSessionHasErrors([
                'per_page' => 'The per page must be an integer.',
            ]);
    }

    public function test_defaults_on_request_class()
    {
        // $defaults = ['sort-desc' => 'body'];

        Post::create(['body' => '1']);
        Post::create(['body' => '2']);
        Post::create(['body' => '3']);

        $this->get('/posts')
            ->assertJsonPath('data.0.body', '3')
            ->assertJsonPath('data.1.body', '2')
            ->assertJsonPath('data.2.body', '1');
    }

    public function test_reserved_filter_keys()
    {
        $this->expectException(ReservedException::class);

        Filter::make('search');
    }

    public function test_appending_queries()
    {
        $filter = Filter::make('account')
            ->withQuery(function ($query) {
                return $query->where('id', 1);
            })->withQuery(function ($query) {
                return $query->orWhere('id', 2);
            });

        $this->assertCount(2, $filter->queries);
    }

    public function test_overriding_queries()
    {
        $filter = Filter::make('account')
            ->withQuery(function ($query) {
                return $query->where('id', 1);
            })->query(function ($query) {
                return $query->orWhere('id', 2);
            });

        $this->assertCount(1, $filter->queries);
    }

    public function test_conditional_when_value_queries()
    {
        $one = Post::factory()->create(['length' => '50']);
        $two = Post::factory()->create(['length' => '100']);

        $this->get('/posts?article-size=50')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $one->id);

        $this->get('/posts?article-size=100')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $two->id);
    }

    public function test_converting_to_cents()
    {
        $one = Post::factory()->create(['length' => '10000']);
        $two = Post::factory()->create(['length' => '20000']);

        $this->get('/posts?money=100')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $one->id);

        $this->get('/posts?money=200')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $two->id);
    }

    public function test_calling_results_twice_is_cached()
    {
        $count = 0;

        DB::listen(function ($query) use (&$count) {
            $count++;
        });

        $request = (new PostFormation());
        $request->results();
        $request->results();

        $this->assertEquals(1, $count);
    }

    public function test_empty_sortable()
    {
        $request = new PostFormation();
        $request->defaults = [];
        $request->results();
        $this->assertTrue(true); // just appeasing test score
    }

    public function test_query_where_condition()
    {
        Post::factory()->create(['title' => 'Good']);
        Post::factory()->create(['title' => 'Bad']);

        $request = new PostFormation();
        $request->where('title', 'Good');
        $results = $request->results();

        $this->assertCount(1, $results);
        $this->assertEquals('Good', $results->first()->title);
    }
}
