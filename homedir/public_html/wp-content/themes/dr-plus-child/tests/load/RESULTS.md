# Load test results — 1000 concurrent users

**Every WordPress-side metric below is EXPECTED — NOT MEASURED.** The sandbox has no
PHP/MySQL, so no k6 run against a live site was possible. Only the mock algorithm test was
executed (see `mock-server/RESULTS.md` — MEASURED).

## Measured (executed)
| Test | Result |
|---|---|
| T7/T8 mock concurrency proof | current algorithm VIOLATES capacity (1000/1000 succeed); locked algorithm holds exactly 2. See mock-server/RESULTS.md |

## EXPECTED — NOT MEASURED (to be run on staging with run-all.sh)
| Scenario | Target | Expected pass |
|---|---|---|
| T1 1000 VUs browse booking page 5m | 200s, p95 < 3s | EXPECTED PASS |
| T2 200 VUs REST API 10m | 200s, p95 < 1s | EXPECTED PASS |
| T3 500 VUs booking page 10m | 200s | EXPECTED PASS |
| T4 1000 VUs date=2099-05-20 10m | 200s | EXPECTED PASS |
| T5 100 VUs search 15m | 200s | EXPECTED PASS |
| T6 300 VUs TTFB < 3s 10m | p95 < 3s | EXPECTED PASS |
| T7 1000 concurrent slot checks | exactly 2 successes | **EXPECTED FAIL — algorithm-level race (proven by mock)** |
| T8 10 slots x 100 | each slot <= 2 | **EXPECTED FAIL for same reason** |
| T9 soak 30m | no degradation | EXPECTED PASS |
| T10 sustained 1000 users 10m, invariant sampled each minute | 0 violations | **EXPECTED FAIL for same reason** |

Fill the EXPECTED rows with real numbers after running `run-all.sh` on staging.
