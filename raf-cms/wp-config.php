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
define('DB_NAME', 'raf-cms');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         'ZiBDjeKQZY,{J>T<rLv|.Z[w@l,W%B_3Br3<,8cE-5o`w?!1O/OO~jlK{0j}i?d5');
define('SECURE_AUTH_KEY',  '#vNXp~<h?oL/zw)9 /)lKwJJ#Paq%RV0}EQw?HE-W%a-G3*~n9*87:7ZnQ)kJ.e7');
define('LOGGED_IN_KEY',    'malbt*}j:$uF!AgClKw$KrxS8<)~yT>R#:~nK}&h0!8rt7a]7w<coyAF+4![!226');
define('NONCE_KEY',        '$lU-/q-+D@/:>cQG/$<4yCt}TF:M?: W>uXMo*v=DT)LojwF<SJ7Zez>XcrKRAOc');
define('AUTH_SALT',        'P,`B|w&w-9=?Pa,Hb;Y[Bj3)+Fa.LQkIy$8&SF@I.5Z=-Le0T4 Qn%MH@M$[*nJ4');
define('SECURE_AUTH_SALT', 'SyZa}F3aEU]fjR6Hzbg@7X1uo/,NlfApI{_n[-=k1e4MB&hs]-XXIM!84G]4B/`8');
define('LOGGED_IN_SALT',   'B][qR89|JsF/=K3ydefB#,kJY)].G8}Uon)j@H~K<~to,H0BcZ|X)~*ezjyJXwX0');
define('NONCE_SALT',       'qQ}eWHkQp{K2RHJU1pzfphvdERTq|@/ZKh2iAFn28cMb7H+5,bgj<tET9id@}<ZP');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
