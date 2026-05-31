# Sync Model

Sync is explicit and conservative. It stores state on local records and never auto-merges
conflicts.

## State

Shared resource sync records use fields such as:

- `remote_id`
- `sync_status`
- `remote_hash`
- `local_hash`
- `synced_at`
- `last_pulled_at`
- `last_pushed_at`
- `conflict_payload`
- `conflicted_at`

Hashes are built from each resource definition's `syncPayload()`, not from raw model attributes.

## Pull

`pull()` lists remote WordPress REST records, maps each payload through `toLocalPayload()`, and
stores or updates local records. If a matching local record is dirty, the pull marks a conflict
instead of overwriting local changes.

## Push

`push()` creates or updates the remote WordPress record using `toRemotePayload()`. If the local
record is dirty and the remote hash no longer matches, push marks a conflict unless the caller is
explicitly forcing local resolution.

## Conflict Resolution

Use explicit resolution methods:

- `resolveConflictUsingLocal($model)` pushes local state with force.
- `resolveConflictUsingRemote($model)` pulls the remote payload for the model's remote ID.
- `resolveConflictManually($model, $payload)` applies a caller-built local payload and clears the
  conflict markers.

Physical media file deletes are outside record sync. Local copied files are deleted only when
`deleteLocalFile()` is called.
