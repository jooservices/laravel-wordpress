# Adding a Resource

Add resources through the shared resource architecture.

1. Create or update a `ResourceDefinition` under the matching `src/Resources/*` namespace.
2. Register the definition in `ResourceRegistry`.
3. Expose the resource through an existing grouped service when there is a natural public entry
   point.
4. Add migrations/model fillable columns only for local state the resource actually needs.
5. Add focused tests for registry access, payload mapping, service construction, and sync behavior.
6. Update README and docs when capability, limitations, or public usage changes.

Do not add routes, controllers, jobs, queues, events, listeners, UI, audit logs, sync history, or
background processing for a resource.

## Payload Rules

`toLocalPayload()` maps WordPress REST payloads into local columns.

`toRemotePayload()` maps local models into editable WordPress REST payloads. Do not send full
rendered REST objects back as editable fields.

`syncPayload()` defines the stable hash input used for conflict detection. Include fields that
represent meaningful synced state and exclude local-only bookkeeping.

## WordPress REST Rules

Author assignment is runtime-permission-dependent. Tests may assert that the payload can include
an author, but a real WordPress site can still assign the authenticated user instead.

Custom meta requires the WordPress site to register keys with `show_in_rest=true`.

Media file upload belongs in `MediaFileService`; do not implement byte transfer inside a resource
definition or record sync helper.
