# Load test plan — 1000 concurrent users

Scope: dr-plus-child capacity module. Test window uses ONLY dates 2099-05-20..2099-05-29
(seeded by setup-staging.sh). Never run against production.

| ID | Scenario | Users | Duration | Pass criterion |
|----|----------|-------|----------|----------------|
| T1 | Browse booking page | 1000 VUs | 5m | 100% HTTP 200, p95 < 3s |
| T2 | REST API load | 200 VUs | 10m | p95 < 1s |
| T3 | Booking page mixed | 500 VUs | 10m | 0 errors |
| T4 | Date-filtered page (2099-05-20) | 1000 VUs | 10m | 100% 200 |
| T5 | Search traffic | 100 VUs | 15m | 100% 200 |
| T6 | TTFB budget | 300 VUs | 10m | p95 TTFB < 3s |
| T7 | **1000 simultaneous slot checks, one slot, capacity 2** | 1000 | burst | **exactly 2 successes** (NON-NEGOTIABLE) |
| T8 | 10 slots x 100 simultaneous each | 1000 total | burst | every slot <= 2 bookings (db-verify) |
| T9 | Soak | 1000 VUs / 30k iterations | 30m | no latency drift |
| T10 | Sustained 1000 users, invariant sampled every minute | 1000 | 10m | **0 invariant violations** (NON-NEGOTIABLE) |

Critical invariant: bookings per hour per clinic never exceed the configured capacity
(default 2; exempt types: phone, chat, video_consultation).

Tooling: k6 for T1–T6, T9; custom Node scripts for T7/T8/T10 (true simultaneity + db check).
Run order: cleanup.sh -> setup-staging.sh -> run-all.sh -> helpers/db-verify.js per slot.
