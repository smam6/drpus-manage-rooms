#!/usr/bin/env bash
# Build the real-WordPress harness: real PHP (wasm) + SQLite driver + WP core +
# WooCommerce + dr-plus/dr-plus-child + the real MySQL dump.
set -euo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
SITE="${WPSITE:-/tmp/wpsite}"
RUN="${WPRUN:-/tmp/realwp}"
SRC="$REPO/homedir/public_html"

echo "==> repo:  $REPO"
echo "==> site:  $SITE"
echo "==> node:  $RUN"

# ---------------------------------------------------------------- 1. PHP (wasm)
mkdir -p "$RUN"
if [ ! -d "$RUN/node_modules/@php-wasm/node" ]; then
  ( cd "$RUN" && npm init -y >/dev/null 2>&1 && npm install --silent \
      @php-wasm/node @php-wasm/universal >/dev/null 2>&1 ) \
    || { echo "npm install failed"; exit 1; }
fi
echo "==> php-wasm ready"

# ------------------------------------------------- 2. WordPress files (symlinked)
mkdir -p "$SITE"
# Copy, never symlink: the php-wasm mount handler does not resolve symlinks
# that point outside the mounted directory.
for d in wp-admin wp-includes; do
  rm -rf "$SITE/$d"
  cp -r "$SRC/$d" "$SITE/"
done
rm -rf "$SITE/wp-content"
mkdir -p "$SITE/wp-content/plugins" "$SITE/wp-content/themes" "$SITE/wp-content/uploads"
cp -r "$SRC/wp-content/plugins/woocommerce" "$SITE/wp-content/plugins/"
cp -r "$SRC/wp-content/themes/dr-plus" "$SITE/wp-content/themes/"
cp -r "$SRC/wp-content/themes/dr-plus-child" "$SITE/wp-content/themes/"
cp "$REPO"/homedir/public_html/wp-*.php "$SITE"/ 2>/dev/null || true
rm -f "$SITE/wp-config.php"
echo "==> WordPress tree assembled ($(find "$SITE" -type f | wc -l) files)"

# --------------------------------------------- 3. SQLite driver (official repo)
SQ="$RUN/sqli"
if [ ! -d "$SQ" ]; then
  git clone --depth 1 https://github.com/WordPress/sqlite-database-integration.git "$SQ" >/dev/null 2>&1 \
    || { echo "clone of sqlite-database-integration failed"; exit 1; }
fi
PLUG="$SITE/wp-content/plugins/sqlite-database-integration"
rm -rf "$PLUG"
cp -r "$SQ/packages/plugin-sqlite-database-integration" "$PLUG"
rm -f "$PLUG/wp-includes/database"                    # broken monorepo symlink
cp -r "$SQ/packages/mysql-on-sqlite/src" "$PLUG/wp-includes/database"
cp "$PLUG/db.copy" "$SITE/wp-content/db.php"
echo "==> sqlite driver installed"

# ------------------------------------------------------------- 4. wp-config.php
cat > "$SITE/wp-config.php" <<'PHPEOF'
<?php
define( 'DB_NAME', 'drplus_local' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );
define( 'DB_ENGINE', 'sqlite' );

$table_prefix = 'Dj3i_';

define( 'AUTH_KEY', 'local-auth-key' );
define( 'SECURE_AUTH_KEY', 'local-secure-auth' );
define( 'LOGGED_IN_KEY', 'local-logged-in' );
define( 'NONCE_KEY', 'local-nonce' );
define( 'AUTH_SALT', 'local-auth-salt' );
define( 'SECURE_AUTH_SALT', 'local-secure-salt' );
define( 'LOGGED_IN_SALT', 'local-logged-salt' );
define( 'NONCE_SALT', 'local-nonce-salt' );

define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_HOME', 'http://localhost:8080' );
define( 'WP_SITEURL', 'http://localhost:8080' );
define( 'DISALLOW_FILE_MODS', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );

if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
require_once ABSPATH . 'wp-settings.php';
PHPEOF
echo "==> wp-config.php written"

# ---------------------------- 5. Neutralise ionCube files in the PARENT theme only
# The parent theme ships 10 encoded files. The capacity module uses none of them.
cd "$SITE/wp-content/themes/dr-plus"
STUBBED=0
for f in $(grep -rl "ionCube" --include="*.php" . 2>/dev/null || true); do
  printf '<?php\n// HARNESS STUB: original was ionCube-encoded; not decodable here.\n// The dr-plus-child capacity module does not include this file.\n' > "$f"
  STUBBED=$((STUBBED+1))
done
echo "==> parent theme: $STUBBED ionCube files stubbed (test environment only)"

echo "==> done. Next: node $RUN/run.mjs ... (see README)"

case "${1:-}" in
  sync)
    # Refresh only the child theme; keep the imported database.
    rm -rf "$SITE/wp-content/themes/dr-plus-child"
    cp -r "$SRC/wp-content/themes/dr-plus-child" "$SITE/wp-content/themes/"
    echo "==> theme synced (database untouched)"
    ;;
  clean)
    # Wipe the database so the next run starts from production truth.
    rm -rf "$SITE"/wp-content/database/.ht.*
    echo "==> database wiped"
    ;;
esac
