# Media Model

Media separates three concerns:

- WordPress attachment records
- local package database records
- optional local physical file copies

`media()->pull()` synchronizes attachment records and source URLs. It does not download file
bytes.

`media()->downloadFile($media)` copies bytes from the attachment source URL into Laravel Storage
and updates local file state.

`media()->deleteLocalFile($media)` deletes the local copied file. Record sync does not delete
physical files.

Full WordPress media upload is not implemented.
