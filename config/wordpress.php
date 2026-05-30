<?php

declare(strict_types=1);

return [
    'table_prefix' => env('LARAVEL_WORDPRESS_TABLE_PREFIX', 'wp_'),

    'connection' => [
        'timeout' => (int) env('WORDPRESS_CONNECTION_TIMEOUT', 15),
        'retries' => (int) env('WORDPRESS_CONNECTION_RETRIES', 1),
    ],

    'sync' => [
        'per_page' => 100,
        'continue_on_error' => true,
        'default_conflict_strategy' => 'mark_conflict',
    ],

    'media' => [
        'disk' => env('WORDPRESS_MEDIA_DISK', 'local'),
        'base_path' => env('WORDPRESS_MEDIA_BASE_PATH', 'wordpress/media'),
        'download_original' => true,
        'overwrite_existing' => false,
        'max_file_size' => 50 * 1024 * 1024,
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'image/svg+xml',
            'application/pdf',
            'text/plain',
            'application/zip',
            'application/json',
        ],
    ],
];
