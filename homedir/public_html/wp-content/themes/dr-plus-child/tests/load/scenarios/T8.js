// T8 — 10 different slots x 100 concurrent users each = 1000 total.
// Pass: every slot ends with at most 2 bookings (verify with helpers/db-verify.js).
import http from 'node:http';
const WP = process.env.WP_URL, PAGE = process.env.BOOKING_PAGE;
async function attack(slotTime) {
  const body = new URLSearchParams({ action: 'drplus_check_slot', date: '2099-05-20', time: slotTime, clinic: '596', nonce: process.env.NONCE || '' });
  let ok = 0;
  await Promise.all(Array.from({ length: 100 }, () => new Promise((res) => {
    const r = http.request(PAGE, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }, (resp) => {
      let b = ''; resp.on('data', (c) => (b += c)); resp.on('end', () => { /ok|available|true/i.test(b) ? ok++ : 0; res(); });
    });
    r.end(body);
  })));
  return ok;
}
(async () => {
  const times = ['08:00:00','09:00:00','10:00:00','11:00:00','12:00:00','13:00:00','14:00:00','15:00:00','16:00:00','17:00:00'];
  const results = await Promise.all(times.map(attack));
  console.log('T8 per-slot successes:', results.join(','));
  const pass = results.every((n) => n <= 2);
  console.log('T8 PASS=' + pass);
  process.exit(pass ? 0 : 1);
})();
