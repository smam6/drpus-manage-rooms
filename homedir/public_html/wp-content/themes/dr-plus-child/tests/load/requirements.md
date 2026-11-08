# Infrastructure requirements — load tests

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| Web server | 4 vCPU, 8 GB RAM | 8 vCPU, 16 GB RAM |
| PHP-FPM workers | 50 | 100 |
| MySQL/MariaDB InnoDB buffer pool | 2 GB | 4 GB |
| Load generator (separate machine) | 4 vCPU, 8 GB RAM | 8 vCPU, 16 GB RAM |
| k6 | v0.49+ | latest |
| Node.js (T7/T8/T10 custom scripts) | v18+ | v20+ |

Notes: file-based PHP sessions (`session.save_handler=files`) are slow at 1000
concurrent — use Redis or DB-backed sessions on staging. Enable OPcache.
Test data uses isolated dates 2099-05-20 .. 2099-05-29 only.
