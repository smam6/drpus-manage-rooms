// Reset test data for the isolated test dates (never touches real dates).
'use strict';
const { execSync } = require('child_process');
const prefix = process.env.WP_PREFIX || 'Dj3i_';
const dates = require('../data/test-dates.json').dates;
for (const d of dates) {
  execSync(`mysql -h${process.env.DB_HOST} -u${process.env.DB_USER} -p${process.env.DB_PASS} ${process.env.DB_NAME} -e "DELETE FROM ${prefix}drplus_booking WHERE \`date\`='${d}'; DELETE FROM ${prefix}drplus_capacity_log WHERE booking_date='${d}';"`);
}
console.log('test data cleaned for dates:', dates.join(', '));
