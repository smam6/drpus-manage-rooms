// T5 k6 scenario — see ../LOAD-TEST-PLAN.md for the goal and pass criteria.
// Env: WP_URL, BOOKING_PAGE. Run: k6 run scenarios/T5.js
import http from 'k6/http';
import { check } from 'k6';
import { getNonce } from '../helpers/nonce.js';
export const options = { vus: 100, duration: '15m' };
export default function () {
  const res = http.get(__ENV.WP_URL + '/?s=clinic');
  check(res, { 'status 200': (r) => r.status === 200 });
}
