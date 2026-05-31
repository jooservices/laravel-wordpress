# Media Files

Media has two separate lifecycles: the WordPress attachment record and the optional local file
copy.

## Attachment Records

`WordPress::site($site)->media()->pull()` pulls WordPress media attachment records through the
shared resource sync layer. The local record stores WordPress fields such as the remote ID, source
URL, MIME/type data, and sync state.

```php
$result = WordPress::site($site)->media()->pull();
```

Pulling media records does not copy bytes into Laravel Storage.

## Local File Copies

Use `downloadFile()` to copy the remote media bytes into Laravel Storage:

```php
$media = WordPress::site($site)->media()->records()->listLocal()->first();

WordPress::site($site)->media()->downloadFile($media);
```

Use `deleteLocalFile()` to delete the local copy:

```php
WordPress::site($site)->media()->deleteLocalFile($media);
```

Record sync never deletes physical files as a side effect. Physical local files are removed only
through `deleteLocalFile()`.

## Unsupported

Full media byte upload to WordPress is not implemented. Do not treat media record push helpers as
file upload support.
