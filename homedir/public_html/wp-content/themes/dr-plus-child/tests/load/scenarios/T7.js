// T7 — 1000 concurrent slot-checks against ONE slot (2099-05-20 08:00, clinic 596).
// Custom Node script (not k6) so we can hold all requests truly simultaneously.
// Pass: exactly 2 of 1000 responses succeed (capacity default = 2).
import http from 'node:http';
const WP = process.env.WP_URL, PAGE = process.env.BOOKING_PAGE;
const N = 1000;
async function main() {
  const body = new URLSearchParams({ action: 'drplus_check_slot', date: '2099-05-20', time: '08:00:00', clinic: '596', nonce: process.env.NONCE || '' });
  let ok = 0, fail = 0;
  const reqs = [];
  const start = Date.now();
  for (let i = 0; i < N; i++) {
    reqs.push(new Promise((res) => {
      const r = http.request(PAGE, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }, (resp) => {
        let b = ''; resp.on('data', (c) => (b += c)); resp.on('end', () => { /ok|available|true/i.test(b) ? ok++ : fail++; res(); });
      });
      r.end(body);
    }));
  }
  await Promise.all(reqs);
  console.log(`T7: ${N} concurrent -> success=${ok} reject=${fail} in ${Date.now() - start}ms  PASS=${ok === 2}`);
  process.exit(ok === 2 ? 0 : 1);
}
main();
