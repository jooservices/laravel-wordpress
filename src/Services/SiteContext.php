<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Comments\CommentService;
use Jooservices\LaravelWordPress\Services\Content\ContentService;
use Jooservices\LaravelWordPress\Services\Editor\EditorService;
use Jooservices\LaravelWordPress\Services\Media\MediaService;
use Jooservices\LaravelWordPress\Services\Navigation\NavigationService;
use Jooservices\LaravelWordPress\Services\RemoteResources\RemoteResourceService;
use Jooservices\LaravelWordPress\Services\System\SystemService;
use Jooservices\LaravelWordPress\Services\Taxonomy\TaxonomyService;
use Jooservices\LaravelWordPress\Services\Users\ApplicationPasswordService;
use Jooservices\LaravelWordPress\Services\Users\UserService;
use Jooservices\LaravelWordPress\Services\Widgets\WidgetService;

final class SiteContext
{
    public function __construct(private readonly Site $site) {}

    public function users(): UserService
    {
        return app(UserService::class, ['site' => $this->site]);
    }

    public function applicationPasswords(): ApplicationPasswordService
    {
        return app(ApplicationPasswordService::class, ['site' => $this->site]);
    }

    public function content(): ContentService
    {
        return app(ContentService::class, ['site' => $this->site]);
    }

    public function media(): MediaService
    {
        return app(MediaService::class, ['site' => $this->site]);
    }

    public function taxonomy(): TaxonomyService
    {
        return app(TaxonomyService::class, ['site' => $this->site]);
    }

    public function comments(): CommentService
    {
        return app(CommentService::class, ['site' => $this->site]);
    }

    public function system(): SystemService
    {
        return app(SystemService::class, ['site' => $this->site]);
    }

    public function editor(): EditorService
    {
        return app(EditorService::class, ['site' => $this->site]);
    }

    public function navigation(): NavigationService
    {
        return app(NavigationService::class, ['site' => $this->site]);
    }

    public function widgets(): WidgetService
    {
        return app(WidgetService::class, ['site' => $this->site]);
    }

    public function remoteResources(): RemoteResourceService
    {
        return app(RemoteResourceService::class, ['site' => $this->site]);
    }
}
