<?php
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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'admin_wordpress_5');

/** MySQL database username */
define('DB_USER', 'wordpress_a');

/** MySQL database password */
define('DB_PASSWORD', 'Sknk_R5Z72');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'qi2(V21Mo%ZX%9bZB0^cax3RHCzwo8rhU0GPK&cYnkXPs)14&Ga)Q2lwYs#8yZ(a');
define('SECURE_AUTH_KEY',  'kB0Lkan3WWl%6BcWSCm#PMpCoMjBLQFFrSNr@PuG6Y%XDRp7jFmkd!tzL(hYNAw3');
define('LOGGED_IN_KEY',    '*WzNoCQbaZOO9u30oS@reWOuNf@syeuN#8EtdyeNeD^1yOYPN!YhQ9PdRR(D5TS*');
define('NONCE_KEY',        'F#KvuyPntRY8)^WQVbxqB#!ZvxuEQnbb^L^SsP%56L578nA*xr)U#d210nnX%APW');
define('AUTH_SALT',        '#sgtSWI6AwSvjpd9wj!CakHB7ySfoV%rZc0jmkE(vHI9%NHIGVzsUAtvzxNcPz2l');
define('SECURE_AUTH_SALT', 'D6444f6Fr@QbJXq9UyjKn7l&HY5eE(4%OfHn4)MqIrrc#0&N9Dlt02vWqVsGsj5v');
define('LOGGED_IN_SALT',   'edW#qEazRbCjWCO6ZsXZa3JVS8u(KAXJ(kFh1eTdTp(SDuRQ3H5OXI0oeDQVLUv6');
define('NONCE_SALT',       'kotA2I#bF*ST)JT5QcReR)ow2kN1IPq%Xysjw7#a2szq3qtP80gdNomSZ1#Q)0SH');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'kpn3Elf_';

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

define( 'WP_ALLOW_MULTISITE', true );

define ('FS_METHOD', 'direct');
