# Changelog

## Unreleased

## 1.0.0 - 2026-05-31

- Stable first release of the Laravel WordPress service-layer package.
- Added package-owned local WordPress records, credentials, resource definitions, repositories,
  grouped site services, and facade entry points for content, media, taxonomy, users, comments,
  system resources, editor resources, navigation, widgets, and dynamic remote resources.
- Added explicit sync lifecycle support for pulling, pushing, conflict detection, local/remote
  resolution, sync state hashes, timestamps, and conflict payloads.
- Split media handling into media attachment records and media file transfers:
  `media()->records()` handles local/remote record CRUD and record sync, while
  `media()->files()` handles upload, download, and local copied-file deletion.
- Added real media upload/download support through `jooservices/wordpress-sdk`, including upload
  source validation, max file size checks, optional MIME allow-list enforcement, attachment
  persistence, and file sync state tracking.
- Added Docker-based real integration workflow for fresh Laravel app installation, package
  discovery, migrations, WP-CLI WordPress installation, post/page pull and push, media record
  pull, explicit media download, real media upload, conflict detection, and idempotent repeated
  pulls.
- Added README, architecture docs, installation/configuration docs, sync docs, media file docs,
  examples, development/testing guides, maintenance guidance, and agent/skill instructions.
- Documented known limitations around WordPress REST permissions, custom meta registration,
  generic resource completeness, endpoint variability, and SDK/WordPress support for remote media
  record updates.
