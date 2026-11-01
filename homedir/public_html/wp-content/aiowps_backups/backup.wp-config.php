<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "aghazga1_new" );

/** Database username */
define( 'DB_USER', "aghazga1_dBaSse1" );

/** Database password */
define( 'DB_PASSWORD', "gRdU&ofJ0AVO0jb2" );

/** Database hostname */
define( 'DB_HOST', "localhost" );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', 'utf8mb4_general_ci' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'os@|?tO]Lk$V5r0k.8|RG%(Br7[[xEM=f!bKXhO1bj/d-U-fuZF+M:ft?sixe#=-');
define('SECURE_AUTH_KEY',  'Slb)?)Ar-_(Edu*:B&$iV$1$RP$-L-15-U-=3x*LFJ{ oF-ja%.-P-:zgB~m|a}9');
define('LOGGED_IN_KEY',    '^VbbY&I&z:pF^A6afo-cm0BoK0>H^c@0G9m[>R<<zcV=^(q=3x |~g;#N.1F*RJ$');
define('NONCE_KEY',        '.A>%&T,%1fUw3uZcVwzq]Tj>#$bdR_*kV-^Z<b I_q0^xA2a+(#C[Gh.(PE^.>-F');
define('AUTH_SALT',        '{SWH93+c6Lq*-fD3&%-CC`j&0A^va^7LW+=+$kH.WrE+|I-[;z>op!r `JX+G`OX');
define('SECURE_AUTH_SALT', 'Ucc!.O6`~cbr:O~B[}+lo5r4gxG2+:G/E0j}+lYxfj{zQjgU49i5b:wmO{*V_5y_');
define('LOGGED_IN_SALT',   '%~t#x(yYOeCOB|RiLe&|jJf;|g9I-=|ky,5yNtR0DMS}>njR}Hg-7yp`4|Xw7Tte');
define('NONCE_SALT',       'MuX,=_<H@-p?@oFZ*S x|`e<EqVZnWaXOZQv%GB>Z:o52x,Uc52W2Z>F@5=LFBMG');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'Dj3i_';


/* Add any custom values between this line and the "stop editing" line. */

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define('DISALLOW_FILE_EDIT', true);
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
