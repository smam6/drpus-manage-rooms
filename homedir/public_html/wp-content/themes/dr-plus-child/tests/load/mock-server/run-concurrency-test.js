// Runs the mock concurrency proof for T7/T10 equivalents in both modes.
// Usage: node run-concurrency-test.js   (writes results to mock-server/RESULTS.md data)
'use strict';
const { attackSlot } = require('./server.js');
(async () => {
  const lines = [];
  const log = (s) => { console.log(s); lines.push(s); };
  // T7 equivalent: 1000 concurrent users, capacity 2
  for (const mode of ['current', 'locked']) {
    const t0 = Date.now();
    const r = await attackSlot('2099-05-20|08:00:00', 1000, 2, mode);
    log(`T7-mock mode=${mode} n=1000 capacity=2 -> successes=${r.successes} rejections=${r.rejections} finalCount=${r.finalCount} invariant=${r.finalCount <= 2 ? 'HELD' : 'VIOLATED'} (${Date.now() - t0}ms)`);
  }
  // T8 equivalent: 10 slots x 100 users
  for (const mode of ['current', 'locked']) {
    let within = 0, violated = 0;
    for (let h = 8; h < 18; h++) {
      const slot = `2099-05-20|${String(h).padStart(2, '0')}:00:00`;
      const r = await attackSlot(slot, 100, 2, mode);
      r.finalCount <= 2 ? within++ : violated++;
    }
    log(`T8-mock mode=${mode} 10 slots x 100 users -> slots within capacity=${within}/10 violations=${violated}`);
  }
  require('fs').writeFileSync(__dirname + '/../results/mock-concurrency-raw.log', lines.join('\n') + '\n');
})();
