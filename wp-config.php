<?php
define( 'WP_CACHE', true );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

define('WP_MEMORY_LIMIT', '256M');

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'id16080459_sumbawa');

/** MySQL database username */
define('DB_USER', 'id16080459_sumbawa1');

/** MySQL database password */
define('DB_PASSWORD', 'vwfZor1RWQ~jXWq=');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '51qzulmvixunatrbnp1uz2m62srpk5igrxlxwhigktjjtuujua9grhumjyhat2we');
define('SECURE_AUTH_KEY',  'exjeqvbrqzn631rvafbtdji1vu1bjigqexg2klspfjgeqaxscq5p1bidc1oxzhfu');
define('LOGGED_IN_KEY',    'cxihu3ohssdz1fjnskewws6e1fr79btrp9zlsyvxapmj2dhwifljkousupjddox7');
define('NONCE_KEY',        '2mhxt7ukrbmkpbxjdx3xc8duvgowm7hptqd96ldy6s2awn6zry8pb4xflctngsem');
define('AUTH_SALT',        'vwvglql6nximh49xm7ehsykg9jnrtehk6uj5kdp1fzdwhdyas5bp7dp7ajs7v9re');
define('SECURE_AUTH_SALT', 'c9xnnefvmfs5onbrofqoa1yokv43laeek1l8bsg5atoecubusyplausvy0mx2r0x');
define('LOGGED_IN_SALT',   'pb1uqkpy51ci7jgf4ku3jlvvc61y79fhixzxyte5w52fsleqaqcvdpcxoamjplzc');
define('NONCE_SALT',       'gq0deqaglgyxiiuiubq4gelziuocksinpkmrrucecjvrytklqc0muuulbb5gdsv4');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpn7_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/**define( 'WP_POST_REVISIONS', 1 );