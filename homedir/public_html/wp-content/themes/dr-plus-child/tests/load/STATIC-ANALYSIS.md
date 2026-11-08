# Static analysis — concurrency safety of the capacity algorithm

Code path: AJAX `drplus_check_slot` -> `check_slot_with_lock()` (inc/capacity/ajax.php)
opens a transaction, `SELECT ... FOR UPDATE` on existing bookings (fallback GET_LOCK),
counts, COMMITs, returns allowed/denied. The parent theme then performs the actual
booking insert AFTER that commit, outside the lock.

## Finding (matches audit residual risk #18, now PROVEN by execution)
Check-then-act across a commit boundary is not atomic. N concurrent requests can each
observe count < capacity before any insert lands => overbooking. The executed mock
(mock-server/RESULTS.md, MEASURED) shows capacity 2 accepting 1000/1000 requests under
the `current` mode, vs exactly 2 under `locked` mode (insert inside lock).

## What the current locking DOES guarantee
Serialization of the count reads (no dirty reads), and protection against two inserts
inside one transaction. It does NOT guarantee the capacity invariant against the parent
insert.

## Recommended fixes (escalated; not applied in this branch)
1. Move the insert (or a capacity-reservation row) into the same locked transaction, or
2. DB-level enforcement: a conditional unique constraint keyed on (clinic, date, hour)
   with a counted row, so an over-capacity insert fails at COMMIT and the client retries.
3. Short-term mitigation: post-insert re-check — after insert, re-count under lock; if
   over capacity, cancel the just-created booking and notify the user.

## Other static observations
- 7x `$wpdb->prepare`, 3x FOR UPDATE, deleted=0 filters: consistent and safe.
- Log table insert per check: fine at low rate, batch at high rate.
- No forbidden patterns (extract/eval/etc.) in the module.
