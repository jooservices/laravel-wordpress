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
            'title' => $this->editableContentValue($model->getAttribute('title')),
            'content' => $this->editableContentValue($model->getAttribute('content')),
            'excerpt' => $this->editableContentValue($model->getAttribute('excerpt')),
            'slug' => $model->getAttribute('slug'),
            'status' => $model->getAttribute('status'),
            'author' => $this->integerValue($model->getAttribute('author')),
            'featured_media' => $this->integerValue($model->getAttribute('featured_media')),
            'meta' => $this->arrayValue($model->getAttribute('meta')),
        ], static fn (mixed $value): bool => $value !== null);

        if ($includeTaxonomies) {
            foreach (['categories', 'tags'] as $field) {
                $ids = $this->idArrayValue($model->getAttribute($field));
                if ($ids !== null) {
                    $payload[$field] = $ids;
                }
            }
        }

        return $payload;
    }

    private function editableContentValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        $payload = $this->arrayValue($value);
        if ($payload === null) {
            return null;
        }

        foreach (['raw', 'rendered'] as $field) {
            if (array_key_exists($field, $payload) && is_scalar($payload[$field])) {
                return (string) $payload[$field];
            }
        }

        return null;
    }

    private function integerValue(mixed $value): ?int
    {
        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : null;
    }

    private function arrayValue(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return $this->payloadArray($value);
        }

        return null;
    }

    private function idArrayValue(mixed $value): ?array
    {
        $payload = $this->arrayValue($value);
        if ($payload === null) {
            return null;
        }

        return array_values(array_map(static fn (int|string $id): int => (int) $id, array_filter(
            $payload,
            static fn (mixed $id): bool => is_int($id) || (is_string($id) && ctype_digit($id)),
        )));
    }
}
