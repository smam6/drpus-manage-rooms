// T2 k6 scenario — see ../LOAD-TEST-PLAN.md for the goal and pass criteria.
// Env: WP_URL, BOOKING_PAGE. Run: k6 run scenarios/T2.js
import http from 'k6/http';
import { check } from 'k6';
import { getNonce } from '../helpers/nonce.js';
export const options = { vus: 200, duration: '10m' };
export default function () {
  const res = http.get(__ENV.WP_URL + '/wp-json/wp/v2/types');
  check(res, { 'status 200': (r) => r.status === 200 });
}
