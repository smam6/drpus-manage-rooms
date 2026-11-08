// T6 k6 scenario — see ../LOAD-TEST-PLAN.md for the goal and pass criteria.
// Env: WP_URL, BOOKING_PAGE. Run: k6 run scenarios/T6.js
import http from 'k6/http';
import { check } from 'k6';
import { getNonce } from '../helpers/nonce.js';
export const options = { vus: 300, duration: '10m' };
export default function () {
  const res = http.get(__ENV.BOOKING_PAGE);
  check(res, { 'status 200': (r) => r.status === 200, 'ttfb<3s': (r) => r.timings.duration < 3000 });
}
