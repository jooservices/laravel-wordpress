<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Media;

use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\MediaItem;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class MediaResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('media', EntityType::MediaItem, 'media_items', MediaItem::class, 'media');
    }

    public function toLocalPayload(array|object $remote): array
    {
        $payload = $this->payloadArray($remote);

        return [
            'remote_id' => isset($payload['id']) ? (int) $payload['id'] : null,
            'status' => $payload['status'] ?? null,
            'slug' => $payload['slug'] ?? null,
            'type' => $payload['type'] ?? null,
            'guid' => $payload['guid'] ?? null,
            'link' => $payload['link'] ?? null,
            'title' => $payload['title'] ?? null,
            'caption' => $payload['caption'] ?? null,
            'alt_text' => $payload['alt_text'] ?? null,
            'description' => $payload['description'] ?? null,
            'author_remote_id' => $payload['author'] ?? null,
            'post_remote_id' => $payload['post'] ?? null,
            'media_type' => $payload['media_type'] ?? null,
            'mime_type' => $payload['mime_type'] ?? null,
            'media_details' => $payload['media_details'] ?? null,
            'source_url' => $payload['source_url'] ?? null,
            'missing_image_sizes' => $payload['missing_image_sizes'] ?? null,
            'remote_file_name' => isset($payload['source_url']) ? basename((string) parse_url((string) $payload['source_url'], PHP_URL_PATH)) : null,
            'date' => $payload['date'] ?? null,
            'date_gmt' => $payload['date_gmt'] ?? null,
            'modified' => $payload['modified'] ?? null,
            'modified_gmt' => $payload['modified_gmt'] ?? null,
            'meta' => $payload['meta'] ?? null,
            'raw_payload' => $payload,
        ];
    }

    public function syncPayload(Model|array $payload): array
    {
        $data = parent::syncPayload($payload);
        unset($data['local_disk'], $data['local_path'], $data['file_name'], $data['file_size'], $data['checksum']);

        return $data;
    }
}
