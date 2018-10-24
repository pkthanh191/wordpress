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
define('DB_NAME', 'test1');

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

define('FS_METHOD', 'direct');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '|/cp>/y.@}ltwWF6s*xKSJ!4CdTjmgHR>Lj(DZC{kAUGEUQT6qK&Hq;Ww!O4Yj0A');
define('SECURE_AUTH_KEY',  'md&p{ZH&_S.^2:aQz}a$`%XG+:77gwwz-JrDj)YcUvFBfC|B#pz@sf{{(<qq1j`#');
define('LOGGED_IN_KEY',    ' NWlNf2mX8*H@B#x*M[^nTU|p|{o.vw;f_*Y@;IR1%|UHluza|8N?q!-XUBgri4~');
define('NONCE_KEY',        '%u*C>^)*PzG|4NDnuUMCB!ZejeGxn=iKX?ts-3`dGVKSE1:`Dh4n+l@ltjoE)]ta');
define('AUTH_SALT',        '4|Z8>&zF[S,U6AhCVLn,mpj9_DbJ=g.t%goAvy]E%pFO^:Xx,Q=E:!WEWFs([!?F');
define('SECURE_AUTH_SALT', '1~/uqpUr5R+mSsnlT[;z5+K0zK_m=y$v<)#/?]<7AqHhmjPpZp:-lvW@,h*w:Zq/');
define('LOGGED_IN_SALT',   'wco8&^R<U7N8wj.[/gkk9`!:BM(R+{Twd~Dng~+VucX=$CO}GW<ru,:0XUAmJ2m,');
define('NONCE_SALT',       'egCv?EG6mx|YRiki|@wMz1d<i.Cn[%ayMyXA2j{`ZP6l*aADCfV7sdgl#GP&_RWL');

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
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

