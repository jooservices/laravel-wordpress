# Sync

Shared resources support explicit pull and push operations.

```php
$posts = WordPress::site($site)->content()->posts();

$posts->pull();
$posts->push($post);
```

`pull()` reads remote WordPress REST payloads, maps them to local payloads, and updates local
records when doing so is safe.

`push()` sends editable local model fields to WordPress. Resource definitions normalize REST
objects before push so rendered or raw response objects are not sent back as editable content.

`sync()` currently aliases pull behavior on shared resources.

## Conflicts

Conflicts are explicit. A dirty local record is not silently overwritten by a remote pull. A dirty
local push checks the stored remote hash and marks a conflict when WordPress changed remotely.

Resolve conflicts through the resource service:

```php
$posts->resolveConflictUsingLocal($post);
$posts->resolveConflictUsingRemote($post);
$posts->resolveConflictManually($post, $payload);
```

The package does not auto-merge conflict payloads.

## WordPress Runtime Limits

Author assignment and registered custom meta depend on the target WordPress runtime. Author fields
can be included in payloads, but WordPress may reject them or assign the authenticated user.
Custom meta must be registered with `show_in_rest=true`.
