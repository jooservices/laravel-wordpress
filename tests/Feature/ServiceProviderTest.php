<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Feature;

use Jooservices\LaravelWordPress\Facades\WordPress;
use Jooservices\LaravelWordPress\Services\Manager;
use Jooservices\LaravelWordPress\Tests\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function test_manager_is_bound_and_facade_resolves(): void
    {
        self::assertInstanceOf(Manager::class, app(Manager::class));
        self::assertNotNull(WordPress::sites());
    }
}
