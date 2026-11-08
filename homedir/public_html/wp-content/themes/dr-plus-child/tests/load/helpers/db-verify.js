// Verify the capacity invariant directly in MySQL after a scenario.
// Usage: node helpers/db-verify.js <slotDate> <startTime> <clinicId> <capacity>
// Env: DB_HOST DB_USER DB_PASS DB_NAME WP_PREFIX (default Dj3i_)
'use strict';
const { execSync } = require('child_process');
const [date, time, clinic, capacity] = process.argv.slice(2);
const prefix = process.env.WP_PREFIX || 'Dj3i_';
const sql = `SELECT COUNT(DISTINCT specialist_id) FROM ${prefix}drplus_booking ` +
  `WHERE office_id='${clinic}' AND \`date\`='${date}' AND start_time='${time}' ` +
  `AND deleted=0 AND order_status NOT IN ('cancelled','refunded','failed','trash');`;
const out = execSync(
  `mysql -h${process.env.DB_HOST} -u${process.env.DB_USER} -p${process.env.DB_PASS} ${process.env.DB_NAME} -N -e "${sql}"`,
  { encoding: 'utf8' }
).trim();
const count = parseInt(out, 10);
const ok = count <= parseInt(capacity, 10);
console.log(`invariant check: slot=${date} ${time} clinic=${clinic} count=${count} capacity=${capacity} -> ${ok ? 'HELD' : 'VIOLATED'}`);
process.exit(ok ? 0 : 1);
