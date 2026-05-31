<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Jooservices\LaravelWordPress\Tests\TestCase;

final class MigrationsTest extends TestCase
{
    public function test_all_expected_tables_exist(): void
    {
        $tables = [
            'sites', 'credentials', 'posts', 'post_revisions', 'post_autosaves', 'pages', 'page_revisions',
            'page_autosaves', 'media_items', 'terms', 'taxonomies', 'users', 'application_passwords',
            'comments', 'settings', 'options', 'post_types', 'post_statuses', 'themes', 'plugins',
            'blocks', 'block_revisions', 'block_autosaves', 'block_types', 'block_patterns',
            'block_pattern_categories', 'global_styles', 'global_style_revisions', 'templates',
            'template_revisions', 'template_parts', 'template_part_revisions', 'navigations',
            'navigation_revisions', 'nav_menus', 'nav_menu_items', 'nav_menu_item_revisions',
            'menu_locations', 'sidebars', 'widgets', 'widget_types', 'remote_resources',
        ];

        foreach ($tables as $table) {
            self::assertTrue(Schema::hasTable('wp_'.$table), $table);
        }
    }
}
