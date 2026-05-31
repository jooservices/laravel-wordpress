# Resource Definition Standards

Resource definitions are adapters between WordPress REST resources and the shared resource
services. They are not public top-level services.

Each definition should provide:

- a stable resource key
- the matching `EntityType`
- the local table and model class
- the remote SDK service for the resource
- the remote ID key
- create, update, and delete support flags
- `toLocalPayload()` for WordPress-to-local mapping
- `toRemotePayload()` for local-to-WordPress mapping
- `syncPayload()` for deterministic hash input

Keep definitions deterministic and side-effect free. Do not add database queries, HTTP calls,
queue dispatching, event dispatching, UI concerns, or logging to resource definitions.

When a resource supports WordPress REST meta, document and test only meta keys that are registered
on the WordPress site with `show_in_rest=true`.
