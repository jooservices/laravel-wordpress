<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Enums;

enum EntityType: string
{
    case Site = 'site';
    case Credential = 'credential';
    case User = 'user';
    case MediaItem = 'media_item';
    case Post = 'post';
    case PostRevision = 'post_revision';
    case PostAutosave = 'post_autosave';
    case Page = 'page';
    case PageRevision = 'page_revision';
    case PageAutosave = 'page_autosave';
    case Term = 'term';
    case Taxonomy = 'taxonomy';
    case ApplicationPassword = 'application_password';
    case Comment = 'comment';
    case Setting = 'setting';
    case Option = 'option';
    case PostType = 'post_type';
    case PostStatus = 'post_status';
    case Theme = 'theme';
    case Plugin = 'plugin';
    case Block = 'block';
    case BlockRevision = 'block_revision';
    case BlockAutosave = 'block_autosave';
    case BlockType = 'block_type';
    case BlockPattern = 'block_pattern';
    case BlockPatternCategory = 'block_pattern_category';
    case GlobalStyle = 'global_style';
    case GlobalStyleRevision = 'global_style_revision';
    case Template = 'template';
    case TemplateRevision = 'template_revision';
    case TemplatePart = 'template_part';
    case TemplatePartRevision = 'template_part_revision';
    case Navigation = 'navigation';
    case NavigationRevision = 'navigation_revision';
    case NavMenu = 'nav_menu';
    case NavMenuItem = 'nav_menu_item';
    case NavMenuItemRevision = 'nav_menu_item_revision';
    case MenuLocation = 'menu_location';
    case Sidebar = 'sidebar';
    case Widget = 'widget';
    case WidgetType = 'widget_type';
    case RemoteResource = 'remote_resource';
}
