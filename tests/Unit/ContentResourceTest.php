<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Unit;

use Jooservices\LaravelWordPress\DTOs\Content\PagePayloadData;
use Jooservices\LaravelWordPress\DTOs\Content\PostPayloadData;
use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Facades\WordPress;
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

    public function test_post_resource_maps_local_model_to_feature_level_payload(): void
    {
        $model = new Post([
            'title' => ['raw' => 'Local title'],
            'content' => ['raw' => 'Local content'],
            'excerpt' => ['raw' => 'Local excerpt'],
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
            'title' => ['raw' => 'Local title'],
            'content' => ['raw' => 'Local content'],
            'excerpt' => ['raw' => 'Local excerpt'],
            'slug' => 'local-title',
            'status' => 'publish',
            'author' => 1,
            'featured_media' => 4,
            'meta' => ['registered_key' => 'value'],
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
