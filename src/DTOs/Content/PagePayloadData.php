<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Content;

final readonly class PagePayloadData
{
    public function __construct(
        public string|array|null $title = null,
        public string|array|null $content = null,
        public string|array|null $excerpt = null,
        public ?string $slug = null,
        public ?string $status = null,
        public ?int $author = null,
        public ?int $featuredMedia = null,
        public ?array $meta = null,
    ) {}

    public function toRemotePayload(): array
    {
        return array_filter([
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'slug' => $this->slug,
            'status' => $this->status,
            'author' => $this->author,
            'featured_media' => $this->featuredMedia,
            'meta' => $this->meta,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
