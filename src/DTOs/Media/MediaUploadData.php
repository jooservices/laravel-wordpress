<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Media;

final readonly class MediaUploadData
{
    public function __construct(
        public ?string $path = null,
        public ?string $disk = null,
        public ?string $diskPath = null,
        public ?string $filename = null,
        public ?string $mimeType = null,
        public ?string $title = null,
        public ?string $caption = null,
        public ?string $description = null,
        public ?string $altText = null,
        public ?int $post = null,
        public ?string $status = null,
        public ?array $meta = null,
    ) {}

    public function toRemotePayload(): array
    {
        return array_filter([
            'filename' => $this->filename,
            'mime_type' => $this->mimeType,
            'title' => $this->title,
            'caption' => $this->caption,
            'description' => $this->description,
            'alt_text' => $this->altText,
            'post' => $this->post,
            'status' => $this->status,
            'meta' => $this->meta,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
