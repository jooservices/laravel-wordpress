# Architecture Overview

Laravel WordPress is a service-layer package above `jooservices/wordpress-sdk`. It coordinates
site credentials, WordPress REST clients, local database records, resource definitions, and
explicit sync state.

The package does not ship an HTTP/API/UI layer. Host Laravel applications decide how to call these
services.

```text
WordPress facade
  Manager
    SiteContext
      grouped services
        ResourceService
          ResourceDefinition
          ResourceLocalService
          ResourceRemoteService
          ResourceSyncService
          SyncStateChecker
```

Feature-level tested resources include posts, pages, media record pull, media file download, media
file upload service boundaries, and dirty-local conflict detection. Generic resource definitions
are available for broader remote, local, and sync operations, but not every definition has
resource-specific feature coverage.
