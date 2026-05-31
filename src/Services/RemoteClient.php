<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services;

use BadMethodCallException;
use JOOservices\WordPress\Sdk\WordPressService as SdkWordPressService;

final class RemoteClient
{
    public function __construct(
        private readonly SdkWordPressService $sdk,
    ) {}

    public function sdk(): SdkWordPressService
    {
        return $this->sdk;
    }

    public function service(string $name): object
    {
        if (! method_exists($this->sdk, $name)) {
            throw new BadMethodCallException("WordPress SDK service [{$name}] is not available.");
        }

        return $this->sdk->{$name}();
    }
}
