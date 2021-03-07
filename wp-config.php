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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'winn-new' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '=#qrl`ep%rqaCcUEau@_jdb?Xmb?&7V5GB~yOM[u}=F&TRf4?x`(;%O;M>/FdTw>' );
define( 'SECURE_AUTH_KEY',  '&Z3xj9pyNCQgca;D*-Kpq@`9R&/},@7<3S>I27zL.:Ka^yhR26SLZb>RQ(poLKtr' );
define( 'LOGGED_IN_KEY',    'eBX*~2nT_~FZ2W$7T*(|_)+drqfeCHx/il-*i=V9LYZ|%w2V<|ZVC8tQFz@#a:IZ' );
define( 'NONCE_KEY',        'X@O1pzXrSFt)[wYRB9A>PD,s7CRE26C]hunM2t9c)@H!:[_(kRyoYG[Wed%kOS4G' );
define( 'AUTH_SALT',        'F`)_rA7r`^pu`UlAG* JoK9>z%}7EG3$&OAg)R1MQngP8~{bZ5oiZOciwMuH^P{_' );
define( 'SECURE_AUTH_SALT', 'q5ZZ(2+c0#C$j =z7J@pAZB!tEB,gvWed[+@(/n6)YpS>4Kh7)q&{|y{a.J-P:g9' );
define( 'LOGGED_IN_SALT',   'g6Q5NbqvaOfg ()[rYRQ|?gFeY )<FDv4^-A.+Ilf $*^/QBNza<t)bWBFUTQ))H' );
define( 'NONCE_SALT',       'rD@Fz;J4!XU[bOj5**:xubYD&-m=&3Z{C[$h!# N#u#M,wTU-}[hp#xExoBSe%9y' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG_LOG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
