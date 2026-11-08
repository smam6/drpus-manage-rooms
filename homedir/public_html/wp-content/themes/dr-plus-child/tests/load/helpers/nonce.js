// Fetch the booking-page HTML once and extract the AJAX nonce from #booking-time data-nonce.
'use strict';
const http = require(process.env.WP_URL && process.env.WP_URL.startsWith('https') ? 'https' : 'http');
async function getNonce(bookingPageUrl) {
  return new Promise((resolve, reject) => {
    http.get(bookingPageUrl, (res) => {
      let body = '';
      res.on('data', (c) => (body += c));
      res.on('end', () => {
        const m = body.match(/id="booking-time"[^>]*data-nonce="([^"]+)"/) || body.match(/data-nonce="([^"]+)"[^>]*id="booking-time"/);
        m ? resolve(m[1]) : reject(new Error('nonce not found in page'));
      });
    }).on('error', reject);
  });
}
module.exports = { getNonce };
