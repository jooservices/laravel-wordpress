<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Unit;

use Jooservices\LaravelWordPress\DTOs\Content\PageCreateData;
use Jooservices\LaravelWordPress\DTOs\Content\PagePayloadData;
use Jooservices\LaravelWordPress\DTOs\Content\PageUpdateData;
use Jooservices\LaravelWordPress\DTOs\Content\PostCreateData;
use Jooservices\LaravelWordPress\DTOs\Content\PostPayloadData;
use Jooservices\LaravelWordPress\DTOs\Content\PostUpdateData;
use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Facades\WordPress;
use Jooservices\LaravelWordPress\Models\Page;
use Jooservices\LaravelWordPress\Models\Post;
use Jooservices\LaravelWordPress\Resources\Content\PageResource;
use Jooservices\LaravelWordPress\Resources\Content\PostResource;
use Jooservices\LaravelWordPress\Tests\TestCase;

final class ContentResourceTest extends TestCase
{
    public function test_content_type_uses_registered_resource_definition(): void
    {
        $site = WordPress::sites()->create([
            'name' => fake()->company(),
            'base_url' => fake()->url(),
        ]);

        self::assertSame('wp_posts', WordPress::site($site)->content()->type('post')->createLocal([])->getTable());
        self::assertSame('wp_pages', WordPress::site($site)->content()->type('page')->createLocal([])->getTable());
    }

    public function test_content_type_rejects_unsupported_type(): void
    {
        $site = WordPress::sites()->create([
            'name' => fake()->company(),
            'base_url' => fake()->url(),
        ]);

        $this->expectException(WordPressException::class);
        $this->expectExceptionMessage('Content type [products] is not supported by this package.');

        WordPress::site($site)->content()->type('products');
    }

    public function test_post_payload_data_maps_to_wordpress_rest_payload(): void
    {
        $payload = new PostPayloadData(
            title: fake()->sentence(),
            content: fake()->paragraph(),
            excerpt: fake()->sentence(),
            slug: fake()->slug(),
            status: 'draft',
            author: 1,
            categories: [2],
            tags: [3],
            featuredMedia: 4,
            meta: ['registered_key' => 'value'],
        );

        self::assertSame([
            'title' => $payload->title,
            'content' => $payload->content,
            'excerpt' => $payload->excerpt,
            'slug' => $payload->slug,
            'status' => 'draft',
            'author' => 1,
            'categories' => [2],
            'tags' => [3],
            'featured_media' => 4,
            'meta' => ['registered_key' => 'value'],
        ], $payload->toRemotePayload());
    }

    public function test_post_create_and_update_data_map_to_wordpress_rest_payload(): void
    {
        $create = new PostCreateData(
            title: fake()->sentence(),
            content: fake()->paragraph(),
            excerpt: fake()->sentence(),
            slug: fake()->slug(),
            status: 'draft',
            author: 1,
            categories: [2],
            tags: [3],
            featuredMedia: 4,
            meta: ['registered_key' => 'value'],
        );
        $update = new PostUpdateData(title: 'Updated', featuredMedia: 5);

        self::assertSame($create->toRemotePayload(), $create->toLocalPayload());
        self::assertSame([
            'title' => $create->title,
            'content' => $create->content,
            'excerpt' => $create->excerpt,
            'slug' => $create->slug,
            'status' => 'draft',
            'author' => 1,
            'categories' => [2],
            'tags' => [3],
            'featured_media' => 4,
            'meta' => ['registered_key' => 'value'],
        ], $create->toRemotePayload());
        self::assertSame(['title' => 'Updated', 'featured_media' => 5], $update->toRemotePayload());
    }

    public function test_page_payload_data_omits_post_taxonomies(): void
    {
        $payload = new PagePayloadData(
            title: fake()->sentence(),
            content: fake()->paragraph(),
            excerpt: fake()->sentence(),
            slug: fake()->slug(),
            status: 'draft',
            author: 1,
            featuredMedia: 4,
            meta: ['registered_key' => 'value'],
        );

        self::assertArrayNotHasKey('categories', $payload->toRemotePayload());
        self::assertArrayNotHasKey('tags', $payload->toRemotePayload());
        self::assertSame(4, $payload->toRemotePayload()['featured_media']);
    }

    public function test_page_create_and_update_data_omit_post_taxonomies(): void
    {
        $create = new PageCreateData(
            title: fake()->sentence(),
            content: fake()->paragraph(),
            excerpt: fake()->sentence(),
            slug: fake()->slug(),
            status: 'draft',
            author: 1,
            featuredMedia: 4,
            meta: ['registered_key' => 'value'],
        );
        $update = new PageUpdateData(content: 'Updated page body');

        self::assertArrayNotHasKey('categories', $create->toRemotePayload());
        self::assertArrayNotHasKey('tags', $create->toRemotePayload());
        self::assertSame(4, $create->toRemotePayload()['featured_media']);
        self::assertSame(['content' => 'Updated page body'], $update->toLocalPayload());
    }

    public function test_content_service_creates_and_updates_post_and_page_from_dtos(): void
    {
        $site = WordPress::sites()->create([
            'name' => fake()->company(),
            'base_url' => fake()->url(),
        ]);
        $content = WordPress::site($site)->content();

        $post = $content->createPost(new PostCreateData(title: 'DTO post', status: 'draft'));
        $page = $content->createPage(new PageCreateData(title: 'DTO page', status: 'draft'));
        $post = $content->updatePost($post, new PostUpdateData(title: 'Updated DTO post'));
        $page = $content->updatePage($page, new PageUpdateData(title: 'Updated DTO page'));

        self::assertInstanceOf(Post::class, $post);
        self::assertInstanceOf(Page::class, $page);
        self::assertSame('Updated DTO post', $post->title);
        self::assertSame('Updated DTO page', $page->title);
    }

    public function test_post_resource_maps_string_local_model_to_feature_level_payload(): void
    {
        $model = new Post([
            'title' => 'Local title',
            'content' => 'Local content',
            'excerpt' => 'Local excerpt',
            'slug' => 'local-title',
            'status' => 'publish',
            'author' => 1,
            'categories' => [2],
            'tags' => [3],
            'featured_media' => 4,
            'meta' => ['registered_key' => 'value'],
            'raw_payload' => ['ignored' => true],
        ]);

        self::assertSame([
            'title' => 'Local title',
            'content' => 'Local content',
            'excerpt' => 'Local excerpt',
            'slug' => 'local-title',
            'status' => 'publish',
            'author' => 1,
            'featured_media' => 4,
            'meta' => ['registered_key' => 'value'],
            'categories' => [2],
            'tags' => [3],
        ], (new PostResource)->toRemotePayload($model));
    }

    public function test_post_resource_normalizes_pulled_rest_objects_to_editable_remote_payload(): void
    {
        $model = new Post([
            'title' => ['raw' => 'Raw title', 'rendered' => 'Rendered title'],
            'content' => ['raw' => 'Raw content', 'rendered' => 'Rendered content', 'protected' => false],
            'excerpt' => ['rendered' => 'Rendered excerpt', 'protected' => false],
            'slug' => 'pulled-title',
            'status' => 'publish',
            'author' => '1',
            'featured_media' => '4',
            'categories' => ['2', 'not-an-id'],
            'tags' => [3],
            'meta' => 'not-an-array',
        ]);

        self::assertSame([
            'title' => 'Raw title',
            'content' => 'Raw content',
            'excerpt' => 'Rendered excerpt',
            'slug' => 'pulled-title',
            'status' => 'publish',
            'author' => 1,
            'featured_media' => 4,
            'categories' => [2],
            'tags' => [3],
        ], (new PostResource)->toRemotePayload($model));
    }

    public function test_page_resource_maps_remote_payload_without_post_taxonomies(): void
    {
        $payload = (new PageResource)->toLocalPayload([
            'id' => 10,
            'title' => ['rendered' => 'Remote page'],
            'content' => ['rendered' => 'Body'],
            'status' => 'draft',
            'author' => 1,
            'featured_media' => 4,
            'categories' => [2],
            'tags' => [3],
        ]);

        self::assertSame(10, $payload['remote_id']);
        self::assertSame(1, $payload['author']);
        self::assertSame(4, $payload['featured_media']);
        self::assertArrayNotHasKey('categories', $payload);
        self::assertArrayNotHasKey('tags', $payload);
    }
}
