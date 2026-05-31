# Laravel WordPress Agent Contract

Use this repository as a Laravel service-layer package above `jooservices/wordpress-sdk`.
It is not an application shell and must not grow HTTP, UI, queue, or background-processing
surface area.

## Scope

- Keep the package focused on local WordPress records, remote WordPress REST access, sync
  state, and explicit media file copies.
- Do not add web routes, API routes, controllers, FormRequests, API resources, jobs, queues,
  events, listeners, audit logs, sync history, background workers, or UI.
- Do not implement media upload unless the user explicitly asks for that feature.
- Keep models thin. Put local database access and coordination in repositories or services.

## Architecture

The public flow is:

```text
WordPress facade -> Manager -> SiteContext -> grouped service -> ResourceService
    -> ResourceDefinition -> ResourceLocalService / ResourceRemoteService / ResourceSyncService
```

- `WordPress::site($site)` resolves a `SiteContext`.
- `SiteContext` exposes grouped services such as `content()`, `media()`, `taxonomy()`,
  `users()`, `comments()`, `system()`, `editor()`, `navigation()`, and `widgets()`.
- Grouped services return shared `ResourceService` instances for resource keys registered in
  `ResourceRegistry`.
- `ResourceDefinition` classes adapt one WordPress resource to local payloads, remote payloads,
  sync payloads, support flags, table names, models, and remote SDK services.
- `ResourceLocalService`, `ResourceRemoteService`, and `ResourceSyncService` own local CRUD,
  remote CRUD, and sync behavior respectively.

## Adding Resources

- Add or update a `ResourceDefinition` under the matching `src/Resources/*` group.
- Register the definition in `ResourceRegistry`.
- Expose it through the existing grouped service only when that group already owns the resource
  family.
- Preserve public method names and signatures unless the user explicitly requests a breaking
  change.
- Use strict types, final/readonly DTO style where DTOs are needed, constructor promotion, and
  explicit payload methods.
- Keep resource definitions as adapters. Do not put network, database, queue, route, or UI logic
  in them.

## Sync Rules

- Sync state is explicit through status fields, hashes, timestamps, and conflict payloads.
- Do not auto-merge conflicts. Use `resolveConflictUsingLocal()`,
  `resolveConflictUsingRemote()`, or `resolveConflictManually()` patterns.
- Dirty local records must not be overwritten silently by remote pulls.
- Remote changes must not be overwritten silently by local pushes unless force/local conflict
  resolution is intentional.
- Physical media files must not be deleted unless `deleteLocalFile()` is called.

## Media Lifecycle

- `media()->pull()` pulls WordPress attachment records and source URLs.
- `media()->downloadFile($media)` copies remote bytes into Laravel Storage and records local file
  state.
- `media()->deleteLocalFile($media)` removes only the local copied file.
- Full media byte upload to WordPress is not implemented. Do not document or build it as
  supported without an explicit feature request.

## WordPress REST Limits

- Author assignment can be sent in post/page payloads, but actual assignment depends on runtime
  WordPress REST permissions and configuration. WordPress may assign the authenticated user.
- Custom meta only round-trips through REST when the WordPress site registers the meta keys with
  `show_in_rest=true`.
- Endpoint behavior can vary by WordPress version, plugins, themes, auth type, and capabilities.

## Testing

- Run package quality through Composer scripts:
  - `composer validate --strict`
  - `composer lint`
  - `composer test`
  - `composer test:unit`
  - `composer test:feature`
  - `composer test:integration`
- Run the real Docker workflow with `./scripts/test-docker.sh` when changes touch sync,
  resources, installation, migrations, package discovery, or integration behavior.
- Preserve Docker artifact names and structure:
  - `artifacts/integration-report.json`
  - `artifacts/integration-summary.txt`
  - `artifacts/junit.xml`
- Do not reduce Docker assertions or replace real WordPress SDK/API behavior with mocks.

## Continuation Rules

- Inspect the actual source before editing.
- Work from latest `develop` when the task requires it.
- Follow existing architecture, Pint style, Composer scripts, and package conventions.
- Stop and ask when requirements are unclear, conflicting, missing, or impossible.
- Update docs when code changes alter the developer contract, resource support, limitations, or
  validation workflow.
