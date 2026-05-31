# Documentation

This documentation supports the package README without changing the package scope. The package is
a Laravel service layer above `jooservices/wordpress-sdk`; it does not provide routes,
controllers, jobs, queues, events, listeners, UI, or background processing.

## Architecture

- [Overview](00-architecture/overview.md)
- [Package scope](00-architecture/package-scope.md)
- [Service architecture](00-architecture/service-architecture.md)
- [Resource definition model](00-architecture/resource-definition-model.md)
- [Sync model](00-architecture/sync-model.md)
- [Media model](00-architecture/media-model.md)
- [Database model](00-architecture/database-model.md)

## Getting Started

- [Installation](01-getting-started/installation.md)
- [Configuration](01-getting-started/configuration.md)

## User Guide

- [Sites](02-user-guide/sites.md)
- [Credentials](02-user-guide/credentials.md)
- [Resource groups](02-user-guide/resource-groups.md)
- [Local CRUD](02-user-guide/local-crud.md)
- [Remote CRUD](02-user-guide/remote-crud.md)
- [Sync](02-user-guide/sync.md)
- [Conflict resolution](02-user-guide/conflict-resolution.md)
- [Media files](02-user-guide/media-files.md)

## Examples

- [Posts and pages](03-examples/posts-pages.md)
- [Posts](03-examples/posts.md)
- [Media](03-examples/media.md)
- [Terms](03-examples/terms.md)
- [Users](03-examples/users.md)

## Development

- [Adding a resource](04-development/adding-resource.md)
- [Resource definition standards](04-development/resource-definition-standards.md)
- [DTO standards](04-development/dto-standards.md)
- [Repository standards](04-development/repository-standards.md)
- [Testing](04-development/testing.md)
- [Docker WordPress workflow](04-development/docker-wordpress.md)

## Maintenance

- [Agent guidelines](05-maintenance/agent-guidelines.md)
- [Risks and gaps](05-maintenance/risks-and-gaps.md)
- [Changelog policy](05-maintenance/changelog-policy.md)
