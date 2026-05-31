<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function table(string $name): string
    {
        return (string) config('wordpress.table_prefix', 'wp_').$name;
    }

    public function up(): void
    {
        Schema::create($this->table('sites'), function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('base_url')->unique();
            $table->string('rest_api_base_url')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('settings')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create($this->table('credentials'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained($this->table('sites'))->cascadeOnDelete();
            $table->string('name')->default('default');
            $table->string('auth_type')->default('application_password')->index();
            $table->string('username')->nullable();
            $table->text('secret')->nullable();
            $table->json('extra')->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['site_id', 'is_default']);
        });

        $this->createUsersTable();
        $this->createMediaItemsTable();

        foreach ($this->simpleEntityTables() as $name) {
            Schema::create($this->table($name), function (Blueprint $table): void {
                $this->addBaseEntityColumns($table);
                $this->addCommonResourceColumns($table);
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse([
            'sites', 'credentials', 'users', 'media_items', ...$this->simpleEntityTables(),
        ]) as $table) {
            Schema::dropIfExists($this->table($table));
        }
    }

    private function createUsersTable(): void
    {
        Schema::create($this->table('users'), function (Blueprint $table): void {
            $this->addBaseEntityColumns($table, withRemoteId: true, withUniqueRemote: false);
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->string('link')->nullable();
            $table->string('locale')->nullable();
            $table->string('nickname')->nullable();
            $table->string('slug')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->json('roles')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('extra_capabilities')->nullable();
            $table->json('avatar_urls')->nullable();
            $table->json('meta')->nullable();
            $table->unique(['site_id', 'remote_id']);
            $table->index(['site_id', 'username']);
            $table->index(['site_id', 'email']);
            $table->index(['site_id', 'slug']);
        });
    }

    private function createMediaItemsTable(): void
    {
        Schema::create($this->table('media_items'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained($this->table('sites'))->cascadeOnDelete();
            $table->unsignedBigInteger('remote_id')->nullable();
            $table->string('status')->nullable();
            $table->string('slug')->nullable();
            $table->string('type')->nullable();
            $table->json('guid')->nullable();
            $table->string('link')->nullable();
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->text('alt_text')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('author_remote_id')->nullable();
            $table->unsignedBigInteger('post_remote_id')->nullable();
            $table->string('media_type')->nullable();
            $table->string('mime_type')->nullable();
            $table->json('media_details')->nullable();
            $table->string('source_url')->nullable();
            $table->json('missing_image_sizes')->nullable();
            $table->string('local_disk')->nullable();
            $table->string('local_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('remote_file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('checksum')->nullable();
            $table->timestamp('date')->nullable();
            $table->timestamp('date_gmt')->nullable();
            $table->timestamp('modified')->nullable();
            $table->timestamp('modified_gmt')->nullable();
            $table->json('meta')->nullable();
            $table->string('record_sync_status')->default('local_only');
            $table->timestamp('record_synced_at')->nullable();
            $table->timestamp('last_record_pulled_at')->nullable();
            $table->timestamp('last_record_pushed_at')->nullable();
            $table->text('last_record_error')->nullable();
            $table->string('file_sync_status')->default('missing');
            $table->timestamp('file_synced_at')->nullable();
            $table->timestamp('last_file_downloaded_at')->nullable();
            $table->timestamp('last_file_uploaded_at')->nullable();
            $table->text('last_file_error')->nullable();
            $table->string('remote_hash')->nullable();
            $table->string('local_hash')->nullable();
            $table->string('file_hash')->nullable();
            $table->string('sync_status')->default('local_only');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('last_pulled_at')->nullable();
            $table->timestamp('last_pushed_at')->nullable();
            $table->json('conflict_payload')->nullable();
            $table->timestamp('conflicted_at')->nullable();
            $table->json('record_conflict_payload')->nullable();
            $table->json('file_conflict_payload')->nullable();
            $table->timestamp('record_conflicted_at')->nullable();
            $table->timestamp('file_conflicted_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'remote_id']);
            $table->index(['site_id', 'media_type']);
            $table->index(['site_id', 'mime_type']);
            $table->index(['site_id', 'post_remote_id']);
            $table->index(['site_id', 'record_sync_status']);
            $table->index(['site_id', 'file_sync_status']);
            $table->index(['site_id', 'sync_status']);
        });
    }

    private function addBaseEntityColumns(Blueprint $table, bool $withRemoteId = true, bool $withUniqueRemote = true): void
    {
        $table->id();
        $table->foreignId('site_id')->constrained($this->table('sites'))->cascadeOnDelete();
        if ($withRemoteId) {
            $table->unsignedBigInteger('remote_id')->nullable();
        }
        $table->string('sync_status')->default('local_only');
        $table->timestamp('synced_at')->nullable();
        $table->timestamp('last_pulled_at')->nullable();
        $table->timestamp('last_pushed_at')->nullable();
        $table->text('last_error')->nullable();
        $table->string('remote_hash')->nullable();
        $table->string('local_hash')->nullable();
        $table->json('conflict_payload')->nullable();
        $table->timestamp('conflicted_at')->nullable();
        $table->json('raw_payload')->nullable();
        $table->timestamps();
        $table->softDeletes();
        $table->index(['site_id', 'sync_status']);
        if ($withUniqueRemote) {
            $table->unique(['site_id', 'remote_id']);
        }
    }

    private function addCommonResourceColumns(Blueprint $table): void
    {
        $table->json('title')->nullable();
        $table->string('name')->nullable();
        $table->string('slug')->nullable();
        $table->string('status')->nullable();
        $table->string('type')->nullable();
        $table->string('link')->nullable();
        $table->text('description')->nullable();
        $table->json('content')->nullable();
        $table->json('excerpt')->nullable();
        $table->json('meta')->nullable();
    }

    private function simpleEntityTables(): array
    {
        return [
            'posts', 'post_revisions', 'post_autosaves',
            'pages', 'page_revisions', 'page_autosaves',
            'terms', 'taxonomies', 'application_passwords', 'comments',
            'settings', 'options', 'post_types', 'post_statuses',
            'themes', 'plugins',
            'blocks', 'block_revisions', 'block_autosaves', 'block_types', 'block_patterns', 'block_pattern_categories',
            'global_styles', 'global_style_revisions',
            'templates', 'template_revisions', 'template_parts', 'template_part_revisions',
            'navigations', 'navigation_revisions', 'nav_menus', 'nav_menu_items', 'nav_menu_item_revisions', 'menu_locations',
            'sidebars', 'widgets', 'widget_types', 'remote_resources',
        ];
    }
};
