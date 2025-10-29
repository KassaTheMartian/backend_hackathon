<?php

namespace Tests\Unit\Services;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Services\PostService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    private $postRepository;
    private $postService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->postRepository = Mockery::mock(PostRepositoryInterface::class);
        $this->postService = new PostService($this->postRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ========== getPosts() Tests ==========

    public function test_get_posts_returns_paginated_posts(): void
    {
        $filters = ['status' => 'published', 'per_page' => 10];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->postRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($paginator);

        $result = $this->postService->getPosts($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_posts_with_empty_filters(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->postRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with([])
            ->andReturn($paginator);

        $result = $this->postService->getPosts();

        $this->assertSame($paginator, $result);
    }

    public function test_get_posts_filters_by_category(): void
    {
        $filters = ['category_id' => 5];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->postRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($paginator);

        $result = $this->postService->getPosts($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_posts_filters_by_featured(): void
    {
        $filters = ['featured' => true];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->postRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($paginator);

        $result = $this->postService->getPosts($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_posts_with_search_query(): void
    {
        $filters = ['search' => 'beauty tips'];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->postRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($paginator);

        $result = $this->postService->getPosts($filters);

        $this->assertSame($paginator, $result);
    }

    // ========== getPostById() Tests ==========

    public function test_get_post_by_id_returns_post(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();
        $post->id = 1;

        $this->postRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($post);

        $result = $this->postService->getPostById(1);

        $this->assertSame($post, $result);
    }

    public function test_get_post_by_id_returns_null_when_not_found(): void
    {
        $this->postRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->postService->getPostById(999);

        $this->assertNull($result);
    }

    // ========== getPostBySlug() Tests ==========

    public function test_get_post_by_slug_returns_post(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();
        $post->slug = ['en' => 'beauty-tips', 'vi' => 'meo-lam-dep'];

        $this->postRepository
            ->shouldReceive('getBySlug')
            ->once()
            ->with('beauty-tips')
            ->andReturn($post);

        $result = $this->postService->getPostBySlug('beauty-tips');

        $this->assertSame($post, $result);
    }

    public function test_get_post_by_slug_returns_null_when_not_found(): void
    {
        $this->postRepository
            ->shouldReceive('getBySlug')
            ->once()
            ->with('non-existent-slug')
            ->andReturn(null);

        $result = $this->postService->getPostBySlug('non-existent-slug');

        $this->assertNull($result);
    }

    public function test_get_post_by_vietnamese_slug(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();
        $post->slug = ['en' => 'beauty-tips', 'vi' => 'meo-lam-dep'];

        $this->postRepository
            ->shouldReceive('getBySlug')
            ->once()
            ->with('meo-lam-dep')
            ->andReturn($post);

        $result = $this->postService->getPostBySlug('meo-lam-dep');

        $this->assertSame($post, $result);
    }

    // ========== getPostWithDetails() Tests ==========

    public function test_get_post_with_details_loads_relationships(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();

        $post->shouldReceive('load')
            ->once()
            ->with(['category', 'tags', 'author'])
            ->andReturnSelf();

        $result = $this->postService->getPostWithDetails($post);

        $this->assertSame($post, $result);
        $this->addToAssertionCount(1); // For shouldReceive verification
    }

    public function test_get_post_with_details_with_locale_parameter(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();

        $post->shouldReceive('load')
            ->once()
            ->with(['category', 'tags', 'author'])
            ->andReturnSelf();

        $result = $this->postService->getPostWithDetails($post, 'en');

        $this->assertSame($post, $result);
        $this->addToAssertionCount(1);
    }

    // ========== createPost() Tests ==========

    public function test_create_post_creates_new_post(): void
    {
        $data = [
            'title' => ['en' => 'New Post', 'vi' => 'Bài viết mới'],
            'slug' => ['en' => 'new-post', 'vi' => 'bai-viet-moi'],
            'content' => ['en' => 'Content...', 'vi' => 'Nội dung...'],
            'status' => 'draft',
            'category_id' => 1,
        ];

        $post = Mockery::mock(Post::class)->makePartial();
        $post->id = 1;

        $this->postRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($post);

        $result = $this->postService->createPost($data);

        $this->assertSame($post, $result);
        $this->assertEquals(1, $result->id);
    }

    public function test_create_post_with_featured_flag(): void
    {
        $data = [
            'title' => ['en' => 'Featured Post', 'vi' => 'Bài nổi bật'],
            'slug' => ['en' => 'featured-post', 'vi' => 'bai-noi-bat'],
            'content' => ['en' => 'Content...', 'vi' => 'Nội dung...'],
            'is_featured' => true,
            'status' => 'published',
        ];

        $post = Mockery::mock(Post::class)->makePartial();

        $this->postRepository
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($inputData) use ($post, $data) {
                $this->assertTrue($inputData['is_featured']);
                return $post;
            });

        $result = $this->postService->createPost($data);

        $this->assertSame($post, $result);
    }

    public function test_create_post_with_multiple_languages(): void
    {
        $data = [
            'title' => [
                'en' => 'Multi-language Post',
                'vi' => 'Bài đa ngôn ngữ',
                'ja' => 'マルチ言語投稿',
            ],
            'slug' => [
                'en' => 'multi-language-post',
                'vi' => 'bai-da-ngon-ngu',
                'ja' => 'maruchi-gengo-toko',
            ],
            'content' => [
                'en' => 'English content...',
                'vi' => 'Nội dung tiếng Việt...',
                'ja' => '日本語の内容...',
            ],
        ];

        $post = Mockery::mock(Post::class)->makePartial();

        $this->postRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($post);

        $result = $this->postService->createPost($data);

        $this->assertSame($post, $result);
    }

    // ========== updatePost() Tests ==========

    public function test_update_post_updates_existing_post(): void
    {
        $data = [
            'title' => ['en' => 'Updated Title', 'vi' => 'Tiêu đề cập nhật'],
            'content' => ['en' => 'Updated content...', 'vi' => 'Nội dung cập nhật...'],
        ];

        $post = Mockery::mock(Post::class)->makePartial();
        $post->id = 1;

        $this->postRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($post);

        $result = $this->postService->updatePost(1, $data);

        $this->assertSame($post, $result);
    }

    public function test_update_post_returns_null_when_not_found(): void
    {
        $data = ['title' => ['en' => 'Title', 'vi' => 'Tiêu đề']];

        $this->postRepository
            ->shouldReceive('update')
            ->once()
            ->with(999, $data)
            ->andReturn(null);

        $result = $this->postService->updatePost(999, $data);

        $this->assertNull($result);
    }

    public function test_update_post_can_change_status(): void
    {
        $data = ['status' => 'published'];

        $post = Mockery::mock(Post::class)->makePartial();
        $post->status = 'published';

        $this->postRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($post);

        $result = $this->postService->updatePost(1, $data);

        $this->assertEquals('published', $result->status);
    }

    // ========== deletePost() Tests ==========

    public function test_delete_post_deletes_post(): void
    {
        $this->postRepository
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->postService->deletePost(1);

        $this->assertTrue($result);
    }

    public function test_delete_post_returns_false_when_not_found(): void
    {
        $this->postRepository
            ->shouldReceive('delete')
            ->once()
            ->with(999)
            ->andReturn(false);

        $result = $this->postService->deletePost(999);

        $this->assertFalse($result);
    }

    // ========== publishPost() Tests ==========

    public function test_publish_post_changes_status_to_published(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();
        $post->status = 'draft';

        $publishedPost = Mockery::mock(Post::class)->makePartial();
        $publishedPost->status = 'published';

        $this->postRepository
            ->shouldReceive('publish')
            ->once()
            ->with($post)
            ->andReturn($publishedPost);

        $result = $this->postService->publishPost($post);

        $this->assertEquals('published', $result->status);
    }

    public function test_publish_post_returns_post_instance(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();

        $this->postRepository
            ->shouldReceive('publish')
            ->once()
            ->with($post)
            ->andReturn($post);

        $result = $this->postService->publishPost($post);

        $this->assertInstanceOf(Post::class, $result);
    }

    // ========== unpublishPost() Tests ==========

    public function test_unpublish_post_changes_status_to_draft(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();
        $post->status = 'published';

        $unpublishedPost = Mockery::mock(Post::class)->makePartial();
        $unpublishedPost->status = 'draft';

        $this->postRepository
            ->shouldReceive('unpublish')
            ->once()
            ->with($post)
            ->andReturn($unpublishedPost);

        $result = $this->postService->unpublishPost($post);

        $this->assertEquals('draft', $result->status);
    }

    public function test_unpublish_post_returns_post_instance(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();

        $this->postRepository
            ->shouldReceive('unpublish')
            ->once()
            ->with($post)
            ->andReturn($post);

        $result = $this->postService->unpublishPost($post);

        $this->assertInstanceOf(Post::class, $result);
    }

    // ========== incrementViews() Tests ==========

    public function test_increment_views_calls_repository(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();

        $this->postRepository
            ->shouldReceive('incrementViews')
            ->once()
            ->with($post);

        $this->postService->incrementViews($post);

        $this->addToAssertionCount(1); // For shouldReceive verification
    }

    public function test_increment_views_does_not_return_value(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();

        $this->postRepository
            ->shouldReceive('incrementViews')
            ->once()
            ->with($post);

        $result = $this->postService->incrementViews($post);

        $this->assertNull($result);
    }

    // ========== getRelatedPosts() Tests ==========

    public function test_get_related_posts_returns_collection(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();
        $post->id = 1;
        $post->category_id = 5;

        $relatedPosts = new Collection([
            Mockery::mock(Post::class)->makePartial(),
            Mockery::mock(Post::class)->makePartial(),
        ]);

        $this->postRepository
            ->shouldReceive('getRelated')
            ->once()
            ->with($post, 4)
            ->andReturn($relatedPosts);

        $result = $this->postService->getRelatedPosts($post);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_related_posts_with_custom_limit(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();

        $relatedPosts = new Collection([
            Mockery::mock(Post::class)->makePartial(),
            Mockery::mock(Post::class)->makePartial(),
            Mockery::mock(Post::class)->makePartial(),
        ]);

        $this->postRepository
            ->shouldReceive('getRelated')
            ->once()
            ->with($post, 3)
            ->andReturn($relatedPosts);

        $result = $this->postService->getRelatedPosts($post, 3);

        $this->assertCount(3, $result);
    }

    public function test_get_related_posts_returns_empty_when_no_related(): void
    {
        $post = Mockery::mock(Post::class)->makePartial();

        $emptyCollection = new Collection([]);

        $this->postRepository
            ->shouldReceive('getRelated')
            ->once()
            ->with($post, 4)
            ->andReturn($emptyCollection);

        $result = $this->postService->getRelatedPosts($post);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    // ========== getFeaturedPosts() Tests ==========

    public function test_get_featured_posts_returns_collection(): void
    {
        $featuredPosts = new Collection([
            Mockery::mock(Post::class)->makePartial(),
            Mockery::mock(Post::class)->makePartial(),
            Mockery::mock(Post::class)->makePartial(),
        ]);

        $this->postRepository
            ->shouldReceive('getFeatured')
            ->once()
            ->with(6)
            ->andReturn($featuredPosts);

        $result = $this->postService->getFeaturedPosts();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
    }

    public function test_get_featured_posts_with_custom_limit(): void
    {
        $featuredPosts = new Collection([
            Mockery::mock(Post::class)->makePartial(),
            Mockery::mock(Post::class)->makePartial(),
        ]);

        $this->postRepository
            ->shouldReceive('getFeatured')
            ->once()
            ->with(2)
            ->andReturn($featuredPosts);

        $result = $this->postService->getFeaturedPosts(2);

        $this->assertCount(2, $result);
    }

    public function test_get_featured_posts_returns_empty_when_none_featured(): void
    {
        $emptyCollection = new Collection([]);

        $this->postRepository
            ->shouldReceive('getFeatured')
            ->once()
            ->with(6)
            ->andReturn($emptyCollection);

        $result = $this->postService->getFeaturedPosts();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    public function test_get_featured_posts_respects_limit(): void
    {
        $featuredPosts = new Collection(array_fill(0, 10, Mockery::mock(Post::class)->makePartial()));

        $this->postRepository
            ->shouldReceive('getFeatured')
            ->once()
            ->with(10)
            ->andReturn($featuredPosts);

        $result = $this->postService->getFeaturedPosts(10);

        $this->assertCount(10, $result);
    }
}
