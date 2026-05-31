<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

use InvalidArgumentException;
use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Resources\Comments\CommentResource;
use Jooservices\LaravelWordPress\Resources\Content\PageAutosaveResource;
use Jooservices\LaravelWordPress\Resources\Content\PageResource;
use Jooservices\LaravelWordPress\Resources\Content\PageRevisionResource;
use Jooservices\LaravelWordPress\Resources\Content\PostAutosaveResource;
use Jooservices\LaravelWordPress\Resources\Content\PostResource;
use Jooservices\LaravelWordPress\Resources\Content\PostRevisionResource;
use Jooservices\LaravelWordPress\Resources\Contracts\ResourceDefinition;
use Jooservices\LaravelWordPress\Resources\Editor\BlockAutosaveResource;
use Jooservices\LaravelWordPress\Resources\Editor\BlockPatternCategoryResource;
use Jooservices\LaravelWordPress\Resources\Editor\BlockPatternResource;
use Jooservices\LaravelWordPress\Resources\Editor\BlockResource;
use Jooservices\LaravelWordPress\Resources\Editor\BlockRevisionResource;
use Jooservices\LaravelWordPress\Resources\Editor\BlockTypeResource;
use Jooservices\LaravelWordPress\Resources\Editor\GlobalStyleResource;
use Jooservices\LaravelWordPress\Resources\Editor\GlobalStyleRevisionResource;
use Jooservices\LaravelWordPress\Resources\Editor\TemplatePartResource;
use Jooservices\LaravelWordPress\Resources\Editor\TemplatePartRevisionResource;
use Jooservices\LaravelWordPress\Resources\Editor\TemplateResource;
use Jooservices\LaravelWordPress\Resources\Editor\TemplateRevisionResource;
use Jooservices\LaravelWordPress\Resources\Media\MediaResource;
use Jooservices\LaravelWordPress\Resources\Navigation\MenuLocationResource;
use Jooservices\LaravelWordPress\Resources\Navigation\NavigationResource;
use Jooservices\LaravelWordPress\Resources\Navigation\NavigationRevisionResource;
use Jooservices\LaravelWordPress\Resources\Navigation\NavMenuItemResource;
use Jooservices\LaravelWordPress\Resources\Navigation\NavMenuItemRevisionResource;
use Jooservices\LaravelWordPress\Resources\Navigation\NavMenuResource;
use Jooservices\LaravelWordPress\Resources\RemoteResources\RemoteResourceDefinition;
use Jooservices\LaravelWordPress\Resources\System\OptionResource;
use Jooservices\LaravelWordPress\Resources\System\PluginResource;
use Jooservices\LaravelWordPress\Resources\System\PostStatusResource;
use Jooservices\LaravelWordPress\Resources\System\PostTypeResource;
use Jooservices\LaravelWordPress\Resources\System\SettingResource;
use Jooservices\LaravelWordPress\Resources\System\ThemeResource;
use Jooservices\LaravelWordPress\Resources\Taxonomy\TaxonomyResource;
use Jooservices\LaravelWordPress\Resources\Taxonomy\TermResource;
use Jooservices\LaravelWordPress\Resources\Users\ApplicationPasswordResource;
use Jooservices\LaravelWordPress\Resources\Users\UserResource;
use Jooservices\LaravelWordPress\Resources\Widgets\SidebarResource;
use Jooservices\LaravelWordPress\Resources\Widgets\WidgetResource;
use Jooservices\LaravelWordPress\Resources\Widgets\WidgetTypeResource;

final class ResourceRegistry
{
    /** @var array<string, ResourceDefinition> */
    private array $definitions = [];

    public function __construct()
    {
        foreach ([
            new UserResource, new ApplicationPasswordResource, new PostResource, new PostRevisionResource,
            new PostAutosaveResource, new PageResource, new PageRevisionResource, new PageAutosaveResource,
            new MediaResource, new TermResource, new TaxonomyResource, new CommentResource, new SettingResource,
            new OptionResource, new PostTypeResource, new PostStatusResource, new ThemeResource, new PluginResource,
            new BlockResource, new BlockRevisionResource, new BlockAutosaveResource, new BlockTypeResource,
            new BlockPatternResource, new BlockPatternCategoryResource, new GlobalStyleResource, new GlobalStyleRevisionResource,
            new TemplateResource, new TemplateRevisionResource, new TemplatePartResource, new TemplatePartRevisionResource,
            new NavigationResource, new NavigationRevisionResource, new NavMenuResource, new NavMenuItemResource,
            new NavMenuItemRevisionResource, new MenuLocationResource, new SidebarResource, new WidgetResource,
            new WidgetTypeResource, new RemoteResourceDefinition,
        ] as $definition) {
            $this->register($definition);
        }
    }

    public function register(ResourceDefinition $definition): void
    {
        $this->definitions[$definition->key()] = $definition;
        $this->definitions[$definition->entityType()->value] = $definition;
    }

    public function get(string|EntityType $key): ResourceDefinition
    {
        $lookup = $key instanceof EntityType ? $key->value : $key;

        return $this->definitions[$lookup] ?? throw new InvalidArgumentException("No resource definition registered for [{$lookup}].");
    }

    public function remote(string $endpoint): RemoteResourceDefinition
    {
        return new RemoteResourceDefinition($endpoint);
    }

    public function all(): array
    {
        return array_values(array_unique($this->definitions, SORT_REGULAR));
    }
}
