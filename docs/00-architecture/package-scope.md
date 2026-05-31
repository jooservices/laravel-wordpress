# Package Scope

This package is a Laravel service layer above `jooservices/wordpress-sdk`.

## In Scope

- site and credential records
- grouped Laravel services for WordPress resource families
- local package-owned database tables
- remote WordPress REST access through the SDK
- local/remote CRUD helpers
- explicit pull/push sync state and conflict markers
- media attachment record pull
- explicit media file download into Laravel Storage

## Out of Scope

- web routes or API routes
- controllers, FormRequests, or API resources
- jobs, queues, events, or listeners
- audit logs or sync history logs
- background processing
- UI
- full media upload to WordPress

Host applications can build those layers above the package if needed.
