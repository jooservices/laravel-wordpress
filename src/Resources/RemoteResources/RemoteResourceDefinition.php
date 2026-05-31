<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\RemoteResources;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\RemoteResource;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class RemoteResourceDefinition extends BaseResourceDefinition
{
    public function __construct(private readonly string $endpoint = '/wp/v2/custom')
    {
        parent::__construct(ltrim($this->endpoint, '/'), EntityType::RemoteResource, 'remote_resources', RemoteResource::class, 'custom', true, true, true);
    }

    public function endpoint(): string
    {
        return $this->endpoint;
    }
}
