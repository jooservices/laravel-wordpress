# Service Architecture

The package exposes a Laravel service layer over `jooservices/wordpress-sdk`.

```text
WordPress facade -> Manager -> SiteContext -> grouped service -> ResourceService
    -> ResourceDefinition -> ResourceLocalService / ResourceRemoteService / ResourceSyncService
```

## Layers

`WordPress` facade resolves the package `Manager`. The manager owns package-level services such
as sites and credentials, and returns a `SiteContext` for a concrete site.

`SiteContext` is the site-scoped entry point. It exposes grouped services for content, media,
taxonomy, users, comments, system, editor, navigation, widgets, application passwords, and dynamic
remote resources.

Grouped services keep the public API readable:

```php
WordPress::site($site)->content()->posts();
WordPress::site($site)->content()->pages();
WordPress::site($site)->media()->records();
WordPress::site($site)->taxonomy()->terms('category');
```

Grouped services create shared `ResourceService` instances through `ResourceServiceFactory`.
The factory resolves a resource key from `ResourceRegistry` and wires the shared local, remote,
sync, and sync-state services.

`ResourceService` provides the common operations:

- remote CRUD through `ResourceRemoteService`
- local CRUD through `ResourceLocalService`
- pull/push/sync through `ResourceSyncService`
- sync-state checks and explicit conflict resolution through `SyncStateChecker`

`ResourceDefinition` classes are adapters. They define the key, entity type, table, model, remote
SDK service, supported operations, and local/remote/sync payload mapping for each WordPress
resource.

## Boundaries

Do not add routes, controllers, jobs, queues, events, listeners, UI, audit logs, sync history, or
background processing to this package. New behavior should fit into the existing service,
resource, repository, DTO, and test structure.
