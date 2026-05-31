# Docker WordPress Workflow

`./scripts/test-docker.sh` runs the maintainability-critical real integration workflow.

The script creates a fresh Laravel app, installs this package through a Composer path repository,
installs WordPress with WP-CLI, seeds real WordPress content and media, runs package migrations,
and executes PHPUnit against the installed package.

The workflow validates:

- package discovery and configuration
- package migrations in a fresh Laravel app
- WP-CLI and WordPress installation
- real WordPress post and media generation
- WordPress-to-Laravel pull sync
- media record pull and explicit file download
- idempotent repeated pulls
- Laravel-originated post create/update push through DTO payload mapping
- Laravel-originated page create push through DTO payload mapping
- taxonomy assignment, featured media assignment, unpublish, and trash reflection
- dirty-local conflict detection against a real remote change

The artifact paths are part of the workflow contract and should not be renamed:

- `artifacts/integration-report.json`
- `artifacts/integration-summary.txt`
- `artifacts/junit.xml`

The report separates WordPress attachments, Laravel media records, and local copied files because
record sync and byte downloads are different behaviors.

Useful environment overrides include `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`,
`WORDPRESS_PATH`, `WORDPRESS_URL`, `LARAVEL_APP_PATH`, and `PACKAGE_PATH`.
