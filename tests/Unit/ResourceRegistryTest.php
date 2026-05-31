<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Unit;

use Jooservices\LaravelWordPress\Facades\WordPress;
use Jooservices\LaravelWordPress\Services\Shared\ResourceRegistry;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;
use Jooservices\LaravelWordPress\Tests\TestCase;

final class ResourceRegistryTest extends TestCase
{
    public function test_it_registers_resource_definitions(): void
    {
        $registry = new ResourceRegistry;

        self::assertGreaterThanOrEqual(39, count($registry->all()));
        self::assertSame('users', $registry->get('users')->key());
        self::assertSame('media', $registry->get('media')->key());
    }

    public function test_resource_service_factory_builds_resource_service_for_site_and_key(): void
    {
        $site = WordPress::sites()->create([
            'name' => fake()->company(),
            'base_url' => fake()->url(),
        ]);

        $service = app(ResourceServiceFactory::class)->make($site, 'posts');

        self::assertInstanceOf(ResourceService::class, $service);
        self::assertSame('wp_posts', $service->createLocal([])->getTable());
    }
}
