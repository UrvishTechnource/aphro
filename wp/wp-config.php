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
define('DB_NAME', 'aphro_blog');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'Techno@123');

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
define('AUTH_KEY',         'pwM1D{)RcokmTGJ}thYa{I?HBOXRP(=l1[HV&LcrC=KgT7qtcuz(rstiQeJLmqn6');
define('SECURE_AUTH_KEY',  '03h)z>2h,F:NQL(l3nH$jY3^;jOX];&sL<}KjS3L0tvuT{D6*Xk,_}getr=.f/nz');
define('LOGGED_IN_KEY',    '[Tm`7qn*d5R:j0$rPDq8|43M{>o6t3N/rD)-&{*6M#$@_>!L6-l!_zn%fC0{PmOD');
define('NONCE_KEY',        'nOH:Pd_t4QaNq;EHRavi}S`y7KY?>q-`Kv^]rKm]0}^cwEscl3Pw+XIj8z6X?aa[');
define('AUTH_SALT',        '7yr2C?TJo:f_g(9hRq wfccg$C,o?>#XT*%gp8&C6]_-]kVEyrn|p<%[n r0]Vtn');
define('SECURE_AUTH_SALT', 'j)=M;SV-?BWL^42yCkag~3tzc^dCc0DIZ}X,]qtO9V50Ui$I7WnZUX!=Zj_;KE{t');
define('LOGGED_IN_SALT',   '}%+!)-]7UhRehO@-r@g6V~zyd+mMnnr>M&isDlmsprX{I[{n!%fv]E^DBp{=;KEn');
define('NONCE_SALT',       'y:RL5Sj%sB49e&&17JP<$Lm4%KniWWg.wZ;r9M99ZUoAQ-4d~.ZZtWHnm4EAh]lS');

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
