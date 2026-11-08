// Login helper for authenticated booking flow (cookie jar).
// Env: WP_URL, TEST_USER, TEST_PASS
'use strict';
const http = require(process.env.WP_URL && process.env.WP_URL.startsWith('https') ? 'https' : 'http');
const qs = require('querystring');
async function login() {
  const url = process.env.WP_URL;
  const body = qs.stringify({ log: process.env.TEST_USER, pwd: process.env.TEST_PASS, 'wp-submit': 'Log In', redirect_to: url + '/wp-admin/' });
  return new Promise((resolve, reject) => {
    const u = new URL(url + '/wp-login.php');
    const req = http.request({ hostname: u.hostname, path: u.pathname, method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Content-Length': Buffer.byteLength(body) } }, (res) => {
      const cookies = (res.headers['set-cookie'] || []).map((c) => c.split(';')[0]).join('; ');
      res.resume();
      res.on('end', () => resolve(cookies));
    });
    req.on('error', reject);
    req.end(body);
  });
}
module.exports = { login };
