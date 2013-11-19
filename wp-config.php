<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wamu_brk2_wp');

/** MySQL database username */
define('DB_USER', 'wamu_brk2_wp');

/** MySQL database password */
define('DB_PASSWORD', 'hvgo9a7X');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');


define('DISABLE_WP_CRON', true);

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'dhU2Zpcq4wIpFFZ9RY2JALIufjtClTbCp1ew9zkHQv1zHyqdov69mLY9V8PQ9cDa');
define('SECURE_AUTH_KEY', 'uwR40ckchQ2gbUYWSJnV3ffX1roFARn3JC29SBQRHhARu5rjrctERm0POlKoIhJv');
define('LOGGED_IN_KEY', 'rhL4c1HrYqfoevIgjmPuKXlhYbq8BsNebzANyDXeDugQcdyoHQQfo2qtA9jGFBty');
define('NONCE_KEY', 'Nni3BueH5KOuSB4NW92wWJWFnYaGePw1lxiuI80B3yO5yumv8zlWh8JQVeVPGJUl');
define('AUTH_SALT', 'bTViRhvvwnF7k6LWcqnlEJAVIAOs9ohPa6ZVbBWuFdG4ZVBlAVcYj3rBL0xZmZKd');
define('SECURE_AUTH_SALT', 'e6XfKpUUpdPlJrUZln4e2NFo3Tq5VtydCGzj2R8VDsbx64hQSNKUkhOrxTQrKeQz');
define('LOGGED_IN_SALT', 'lI0FGqXnpBv51my0Hj0drDTpZvT5ZkiMpL9iozChmVDPbP3BWHpp8kYBVpFK5ggJ');
define('NONCE_SALT', 'ozdBrta4G5Mu35SKUiuvpAQ4WHoeUMhmCHLyEIlqmciIb0rbXKS1CUMFzeq1fs82');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
