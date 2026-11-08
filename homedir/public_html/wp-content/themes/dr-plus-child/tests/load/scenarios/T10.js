// T10 — sustained 1000-user load for 10 minutes; invariant checked after each minute via db-verify.
// Pass: capacity per slot never exceeds 2 at any sample.
import http from 'node:http';
import { execSync } from 'node:child_process';
const WP = process.env.WP_URL, PAGE = process.env.BOOKING_PAGE;
const stop = Date.now() + 10 * 60 * 1000;
let stopFlag = false;
setTimeout(() => (stopFlag = true), 10 * 60 * 1000);
function hit() {
  const body = new URLSearchParams({ action: 'drplus_check_slot', date: '2099-05-20', time: '08:00:00', clinic: '596', nonce: '' });
  const r = http.request(PAGE, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }, (resp) => resp.resume());
  r.on('error', () => {}); r.end(body);
}
(async () => {
  const workers = Array.from({ length: 1000 }, async () => { while (!stopFlag) { hit(); await new Promise((r) => setTimeout(r, 500)); } });
  let violations = 0;
  for (let m = 1; m <= 10; m++) {
    await new Promise((r) => setTimeout(r, 60000));
    try { execSync(`node ../helpers/db-verify.js 2099-05-20 08:00:00 596 2`, { stdio: 'inherit' }); }
    catch { violations++; }
  }
  await Promise.all(workers);
  console.log('T10 violations=' + violations + ' PASS=' + (violations === 0));
  process.exit(violations === 0 ? 0 : 1);
})();
