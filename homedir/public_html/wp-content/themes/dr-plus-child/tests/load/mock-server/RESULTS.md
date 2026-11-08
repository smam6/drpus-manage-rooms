# Mock concurrency test — MEASURED (executed in sandbox, Node v22.22.3)

Date: 2026-11-06. Machine: sandbox (no PHP/MySQL needed; pure algorithm mock).
The mock reproduces `check_slot_with_lock` + parent insert-after-COMMIT.

## T7 equivalent — 1000 concurrent users, one slot (2099-05-20 08:00), capacity 2
| Mode (algorithm) | successes | rejections | final bookings in slot | invariant |
|---|---|---|---|---|
| `current` (check under FOR UPDATE lock, insert AFTER commit) | 1000 | 0 | 1000 | **VIOLATED** |
| `locked` (check AND insert inside the lock) | 2 | 998 | 2 | HELD |

Reproduced 10/10 runs each for n=100 (identical pattern), plus one n=1000 run.

## T8 equivalent — 10 slots x 100 users
| Mode | slots within capacity | violations |
|---|---|---|
| `current` | 0/10 | 10 |
| `locked` | 10/10 | 0 |

## Conclusion (ESCALATION — residual audit risk #18 is REAL at algorithm level)
The check-then-act design cannot guarantee "capacity per hour per clinic <= configured value"
when the booking insert happens after the lock transaction commits. Every concurrent request
passes the count check before any insert lands, so a slot with capacity 2 accepts all comers.
In real MySQL the overbook factor will be smaller than the mock (requests serialize on the
lock, and inserts land in between), but it is strictly greater than 1 — the invariant is not
guaranteed. Proposed fixes (NOT applied here, feature code is out of scope for this branch):
1. Hold a DB lock (SELECT ... FOR UPDATE on the capacity row, or GET_LOCK) across the actual
   insert, i.e. move the insert into the same transaction as the check; or
2. Enforce at the database level: unique/conditional constraint so an insert that would exceed
   capacity fails and triggers a re-check/refund path.
Raw log: `results/mock-concurrency-raw.log`.
