<?php

declare(strict_types=1);

if (! function_exists('laravel_wordpress_table')) {
    function laravel_wordpress_table(string $name): string
    {
        return (string) config('wordpress.table_prefix', 'wp_').$name;
    }
}
