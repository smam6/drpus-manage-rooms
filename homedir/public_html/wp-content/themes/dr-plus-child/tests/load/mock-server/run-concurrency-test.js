// Runs the mock concurrency proof (T7/T8/T10 equivalents) for a given mode.
// Usage: node run-concurrency-test.js [--mode=locked-v2]
// Modes: current (pre-1.0.1, expected to VIOLATE), locked (baseline), locked-v2 (v1.0.1 fix).
// Writes raw output to ../results/mock-concurrency-raw.log
'use strict';
const { attackSlot } = require('./server.js');

const args = process.argv.slice(2);
const modeArg = args.find((a) => a.startsWith('--mode='));
const mode = modeArg ? modeArg.split('=')[1] : 'locked-v2';

(async () => {
  const lines = [];
  const log = (s) => { console.log(s); lines.push(s); };
  log(`# mock concurrency run — mode=${mode} — ${new Date().toISOString()}`);

  // T7: 1000 concurrent users, one slot, capacity 2 -> expect exactly 2 successes
  const t7 = await attackSlot('2099-05-20|08:00:00', 1000, 2, mode);
  log(`T7 mode=${mode} n=1000 capacity=2 -> successes=${t7.successes} rejections=${t7.rejections} finalCount=${t7.finalCount} invariant=${t7.finalCount <= 2 ? 'HELD' : 'VIOLATED'}`);

  // T10: 10 rounds, each must produce exactly 2 successes
  let t10exact = 0;
  for (let r = 1; r <= 10; r++) {
    const res = await attackSlot(`2099-05-20|10:00:00`, 1000, 2, mode);
    if (res.successes === 2 && res.finalCount === 2) t10exact++;
    log(`T10 round ${r}: mode=${mode} n=1000 -> successes=${res.successes} rejections=${res.rejections} finalCount=${res.finalCount}`);
  }
  log(`T10 mode=${mode}: rounds with exactly 2 successes = ${t10exact}/10`);

  // T8: 10 slots x 100 users -> each slot exactly 2, total 20
  let total = 0, slotsExact = 0;
  for (let h = 8; h < 18; h++) {
    const slot = `2099-05-20|${String(h).padStart(2, '0')}:00:00`;
    const r = await attackSlot(slot, 100, 2, mode);
    total += r.successes;
    if (r.successes === 2 && r.finalCount === 2) slotsExact++;
  }
  log(`T8 mode=${mode}: 10 slots x 100 -> total successes=${total}, slots with exactly 2 = ${slotsExact}/10`);

  require('fs').writeFileSync(__dirname + '/../results/mock-concurrency-raw.log', lines.join('\n') + '\n');
})();
