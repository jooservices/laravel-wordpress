<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Unit;

use Jooservices\LaravelWordPress\Services\Shared\PayloadHasher;
use Jooservices\LaravelWordPress\Tests\TestCase;

final class PayloadHasherTest extends TestCase
{
    public function test_it_hashes_payloads_deterministically(): void
    {
        $hasher = new PayloadHasher;

        self::assertSame(
            $hasher->hash(['b' => 2, 'a' => ['d' => 4, 'c' => 3], 'updated_at' => now()]),
            $hasher->hash(['a' => ['c' => 3, 'd' => 4], 'b' => 2]),
        );
    }
}
