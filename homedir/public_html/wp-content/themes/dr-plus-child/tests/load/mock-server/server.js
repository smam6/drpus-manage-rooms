/**
 * Mock concurrency server — Room Capacity Management load tests.
 *
 * Simulates the PHP algorithm of inc/capacity/class-capacity-repository.php:
 *   check_slot_with_lock() = mutex-protected count, then lock released,
 *   and (as in the real feature) the INSERT happens later OUTSIDE the lock,
 *   because the parent theme owns the insert.
 *
 * Two modes:
 *   mode=current  → check under lock, insert AFTER lock release (current design)
 *   mode=locked   → insert INSIDE the lock (proposed fix)
 *
 * The invariant: distinct successful bookings per slot must never exceed capacity.
 *
 * @package DrPlusChild\Capacity\Tests
 */
'use strict';

// Simple async mutex (FIFO queue).
class Mutex {
  constructor() { this.queue = []; this.locked = false; }
  async acquire() {
    if (!this.locked) { this.locked = true; return; }
    await new Promise((resolve) => this.queue.push(resolve));
    this.locked = true;
  }
  release() {
    const next = this.queue.shift();
    if (next) { next(); } else { this.locked = false; }
  }
}

// In-memory "Dj3i_drplus_booking" table: Map slotKey -> Set of specialistIds.
const bookings = new Map();
const slotMutexes = new Map();
function mutexFor(slotKey) {
  if (!slotMutexes.has(slotKey)) slotMutexes.set(slotKey, new Mutex());
  return slotMutexes.get(slotKey);
}

function countBooked(slotKey) {
  return (bookings.get(slotKey) || new Set()).size;
}

/**
 * Mirrors DrPlus_Child_Capacity_Repository::check_slot_with_lock() + the parent insert.
 * mode 'current': insert happens after the lock is released (real architecture).
 * mode 'locked':  insert happens inside the lock (proposed fix).
 */
async function book(slotKey, specialistId, capacity, mode) {
  if (mode === 'locked-v2') {
    // v1.0.1 Layer 1: GET_LOCK held across count AND the parent's insert.
    const mu = mutexFor(slotKey);
    await mu.acquire(); // SELECT GET_LOCK(..., 5)
    let allowed, rolledBack = false;
    try {
      allowed = countBooked(slotKey) < capacity; // count_distinct_specialists()
      if (allowed) {
        // parent theme INSERT happens while the advisory lock is still held
        if (!bookings.has(slotKey)) bookings.set(slotKey, new Set());
        bookings.get(slotKey).add(specialistId);
      }
      // Layer 2: compensating check after insert — re-count, roll back own booking if over
      if (allowed && countBooked(slotKey) > capacity) {
        bookings.get(slotKey).delete(specialistId);
        rolledBack = true;
      }
    } finally {
      mu.release(); // RELEASE_LOCK after woocommerce_checkout_order_processed
    }
    if (!allowed) return { ok: false, reason: 'capacity_exceeded' };
    if (rolledBack) return { ok: false, reason: 'compensating_rollback' };
    return { ok: true };
  }

  const mu = mutexFor(slotKey);
  await mu.acquire();
  let allowed;
  try {
    allowed = countBooked(slotKey) < capacity; // SELECT ... FOR UPDATE + COUNT
    if (allowed && mode === 'locked') {
      // proposed fix: insert inside the critical section
      if (!bookings.has(slotKey)) bookings.set(slotKey, new Set());
      bookings.get(slotKey).add(specialistId);
    }
  } finally {
    mu.release(); // COMMIT / ROLLBACK
  }
  if (!allowed) return { ok: false, reason: 'capacity_exceeded' };
  if (mode === 'current') {
    // parent theme inserts AFTER our transaction committed (residual risk #18)
    await new Promise((r) => setImmediate(r)); // simulate gap between COMMIT and INSERT
    if (!bookings.has(slotKey)) bookings.set(slotKey, new Set());
    bookings.get(slotKey).add(specialistId);
  }
  return { ok: true };
}

/**
 * Fire N concurrent booking attempts at one slot.
 * Returns { successes, rejections, finalCount }.
 */
async function attackSlot(slotKey, n, capacity, mode) {
  bookings.delete(slotKey);
  const results = await Promise.all(
    Array.from({ length: n }, (_, i) => book(slotKey, 1000 + i, capacity, mode))
  );
  const successes = results.filter((r) => r.ok).length;
  return { successes, rejections: n - successes, finalCount: countBooked(slotKey) };
}

module.exports = { attackSlot, countBooked };

// Run directly: node server.js <mode> <n> <capacity> <rounds>
if (require.main === module) {
  const mode = process.argv[2] || 'current';
  const n = parseInt(process.argv[3] || '1000', 10);
  const capacity = parseInt(process.argv[4] || '2', 10);
  const rounds = parseInt(process.argv[5] || '1', 10);
  (async () => {
    let allOk = true;
    for (let r = 1; r <= rounds; r++) {
      const res = await attackSlot(`2099-05-20|10:00:00`, n, capacity, mode);
      const pass = res.finalCount <= capacity && res.successes === Math.min(capacity, n);
      if (!pass) allOk = false;
      console.log(
        `round ${r}: mode=${mode} n=${n} capacity=${capacity} -> successes=${res.successes} rejections=${res.rejections} finalCount=${res.finalCount} invariant=${pass ? 'HELD' : 'VIOLATED'}`
      );
    }
    process.exit(allOk ? 0 : 1);
  })();
}
