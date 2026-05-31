<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Unit;

use Jooservices\LaravelWordPress\Services\Shared\ResourceRegistry;
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
}
