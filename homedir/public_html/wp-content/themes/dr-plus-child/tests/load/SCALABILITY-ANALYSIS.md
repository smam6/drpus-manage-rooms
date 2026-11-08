# Scalability analysis — 1000 concurrent users

## Bottlenecks (ranked)
1. **PHP-FPM worker pool** — 1000 simultaneous PHP requests need >=100 busy workers; queueing
   starts at worker exhaustion. Recommend 100 workers + OPcache + Redis-backed sessions.
2. **MySQL row locks on the booking table** — FOR UPDATE in check_slot_with_lock serializes
   slot checks; under 1000-way contention on one slot this is a hotspot but bounded (each check
   is ms-scale). See STATIC-ANALYSIS.md for the correctness race this lock does NOT prevent.
3. **Session storage** — file sessions degrade sharply at 1000 concurrent; use Redis.
4. **GET_LOCK fallback** — if InnoDB row locks fail, named locks serialize per slot; fine.
5. **Log table writes** (`drplus_capacity_log`) — one row per check; at 1000 rps use batched
   inserts or async queue to avoid write contention.

## Horizontal scaling
Stateless web tier scales linearly behind a load balancer; MySQL is the single writer —
use one primary; read replicas offload T2/T5. Capacity correctness must live in the primary.

## Capacity math
Capacity 2/hour/clinic with 20 specialists => theoretical max 40 bookings/hour/clinic
only if every slot were used; in practice far lower. 1000 concurrent users against one slot
is an adversarial case, not steady state — but the invariant must still hold (T7/T10).
