# Media Model

Media separates three concerns:

- WordPress attachment records
- local package database records
- optional local physical file copies

`media()->records()->pull()` synchronizes attachment records and source URLs. It does not download file
bytes.

`media()->files()->download($media)` copies bytes from the attachment source URL into Laravel Storage
and updates local file state.

`media()->files()->deleteLocal($media)` deletes the local copied file. Record sync does not delete
physical files.

`media()->files()->upload($data)` uploads real bytes to WordPress and persists the returned
attachment record locally.
