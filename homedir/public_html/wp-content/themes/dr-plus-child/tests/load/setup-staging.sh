#!/usr/bin/env bash
# Prepare an isolated staging copy. NEVER run against production.
set -eu
: "${DB_HOST:?}${DB_USER:?}${DB_PASS:?}${DB_NAME:?}${WP_URL:?}${WP_PREFIX:=Dj3i_}"
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" <<SQL
-- seed 20 test specialists (post_type doctor) and 3 test clinics
SQL
node helpers/cleanup.js
echo "Staging ready. Test window: 2099-05-20 .. 2099-05-29 only."
