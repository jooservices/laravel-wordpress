# Posts and Pages

Posts and pages are the most feature-level tested content resources.

```php
use Jooservices\LaravelWordPress\Facades\WordPress;

$content = WordPress::site($site)->content();

$content->posts()->pull();
$content->pages()->pull();
```

## Local Create and Push

```php
$post = $content->posts()->createLocal([
    'title' => 'Release notes',
    'content' => 'Published from Laravel.',
    'status' => 'draft',
    'slug' => 'release-notes',
]);

$content->posts()->push($post);
```

Post/page payload mapping supports title, content, excerpt, slug, status, author, featured media,
and registered REST meta. Posts also support category and tag IDs.

## Runtime Limits

WordPress can reject or override author assignment depending on the authenticated user's REST
permissions. Custom meta only appears in REST payloads when WordPress registers the meta keys with
`show_in_rest=true`.
