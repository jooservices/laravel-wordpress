<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Content\Concerns;

use Illuminate\Database\Eloquent\Model;

trait MapsContentPayload
{
    protected function contentLocalPayload(array|object $remote, bool $includeTaxonomies): array
    {
        $payload = $this->payloadArray($remote);
        $localPayload = parent::toLocalPayload($payload) + array_filter([
            'author' => $payload['author'] ?? null,
            'featured_media' => $payload['featured_media'] ?? null,
        ], static fn (mixed $value): bool => $value !== null);

        if ($includeTaxonomies) {
            foreach (['categories', 'tags'] as $field) {
                if (array_key_exists($field, $payload)) {
                    $localPayload[$field] = $payload[$field];
                }
            }
        }

        return $localPayload;
    }

    protected function contentRemotePayload(Model $model, bool $includeTaxonomies): array
    {
        $payload = array_filter([
            'title' => $model->getAttribute('title'),
            'content' => $model->getAttribute('content'),
            'excerpt' => $model->getAttribute('excerpt'),
            'slug' => $model->getAttribute('slug'),
            'status' => $model->getAttribute('status'),
            'author' => $model->getAttribute('author'),
            'featured_media' => $model->getAttribute('featured_media'),
            'meta' => $model->getAttribute('meta'),
        ], static fn (mixed $value): bool => $value !== null);

        if ($includeTaxonomies) {
            foreach (['categories', 'tags'] as $field) {
                if ($model->getAttribute($field) !== null) {
                    $payload[$field] = $model->getAttribute($field);
                }
            }
        }

        return $payload;
    }
}
