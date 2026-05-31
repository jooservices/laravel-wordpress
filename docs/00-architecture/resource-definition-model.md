# Resource Definition Model

`ResourceDefinition` classes adapt WordPress REST resources into local sync behavior.

They define:

- resource key and `EntityType`
- local table and model class
- remote SDK service
- remote ID key
- operation support flags
- local payload mapping
- remote payload mapping
- sync payload hashing input

Definitions are registered in `ResourceRegistry`. Grouped services request resources by key
through `ResourceServiceFactory`, which builds a shared `ResourceService` for the current site.

Definitions should stay deterministic and side-effect free. Database access belongs in local
services or repositories; remote access belongs in remote services and the SDK.
