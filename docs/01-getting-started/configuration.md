# Configuration

Configure table prefix, connection retries, sync defaults, and media storage.

| Key | Default | Purpose |
| --- | --- | --- |
| `table_prefix` | `wp_` | Prefix for package-owned Laravel tables. |
| `connection.timeout` | `15` | Timeout, in seconds, for remote WordPress SDK calls. |
| `connection.retries` | `1` | Retry count passed to the SDK HTTP client. |
| `sync.per_page` | `100` | Default page size for pull/sync operations. |
| `sync.continue_on_error` | `true` | Continue processing later records after a per-record sync failure. |
| `sync.default_conflict_strategy` | `mark_conflict` | Default conflict behavior used by sync workflows. |
| `media.disk` | `local` | Laravel filesystem disk for downloaded media files. |
| `media.base_path` | `wordpress/media` | Base storage path for downloaded media files. |
| `media.download_original` | `true` | Reserved flag for preferring original media files. |
| `media.overwrite_existing` | `false` | Reserved flag for local overwrite behavior. |
| `media.max_file_size` | `52428800` | Maximum upload source and downloaded media size in bytes. |
| `media.allowed_mime_types` | common image, PDF, text, ZIP, JSON types | MIME allow-list checked for uploads and when downloads expose a content type. |
