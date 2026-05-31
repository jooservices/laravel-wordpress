# Media Files

Media has two separate lifecycles: the WordPress attachment record and the optional local file
copy.

## Attachment Records

`WordPress::site($site)->media()->records()->pull()` pulls WordPress media attachment records through the
shared resource sync layer. The local record stores WordPress fields such as the remote ID, source
URL, MIME/type data, and sync state.

```php
$result = WordPress::site($site)->media()->records()->pull();
```

Pulling media records does not copy bytes into Laravel Storage.

Remote attachment deletion is available through `records()->deleteRemote($remoteId, force: true)`.
Remote record creation requires bytes and is handled by `files()->upload(...)`; remote record
update is not exposed by the installed SDK media service.

## Local File Copies

Use `files()->download()` to copy the remote media bytes into Laravel Storage:

```php
$media = WordPress::site($site)->media()->records()->listLocal()->first();

WordPress::site($site)->media()->files()->download($media);
```

Use `files()->deleteLocal()` to delete the local copy:

```php
WordPress::site($site)->media()->files()->deleteLocal($media);
```

Record sync never deletes physical files as a side effect. Physical local files are removed only
through `files()->deleteLocal()` or the backward-compatible `deleteLocalFile()` alias.

## Uploads

`files()->upload()` sends real file bytes to the WordPress Media Library through
`jooservices/wordpress-sdk`, then stores or updates the returned attachment record locally.

```php
use Jooservices\LaravelWordPress\DTOs\Media\MediaUploadData;

$media = WordPress::site($site)->media()->files()->upload(new MediaUploadData(
    path: storage_path('app/example.jpg'),
    filename: 'example.jpg',
    title: 'Example',
    altText: 'Example alt text',
));
```

`uploadFile()` is kept only as a backward-compatible alias for `files()->upload(...)` and accepts
`MediaUploadData`. It no longer pushes media records while pretending to upload bytes.
