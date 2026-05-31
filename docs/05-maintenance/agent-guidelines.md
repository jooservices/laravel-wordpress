# Agent Guidelines

Before changing code, inspect the current source and tests. Work from the repository's current
architecture instead of implementing from prompts alone.

## Required Posture

- Keep this package as a service layer above `jooservices/wordpress-sdk`.
- Preserve public API entry points such as `WordPress::site($site)->content()->posts()`,
  `pages()`, `media()`, `taxonomy()`, and `users()`.
- Follow existing DTO, service, repository, migration, Pint, and Composer script conventions.
- Stop and ask when requirements are unclear, conflicting, missing, or impossible.
- Update docs when code changes alter capability, architecture, testing, or limitations.

## Forbidden Additions

Do not add web/API routes, controllers, FormRequests, API resources, jobs, queues, events,
listeners, UI, audit logs, sync history, or background processing.

Do not implement media upload unless explicitly requested.

## Validation

Run the narrowest useful tests while developing, then run the requested validation suite before
reporting done. For maintainability or sync/resource changes, include:

```bash
composer validate --strict
composer lint
composer test
composer test:unit
composer test:feature
composer test:integration
./scripts/test-docker.sh
```

If a command cannot run, report the exact command, reason, and what remains unvalidated.
