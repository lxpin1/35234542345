<?php
 // By Speed Optimizer by SiteGround

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
define( 'DB_NAME', '2forks_test2');

/** Database username */
define( 'DB_USER', '2forks_new');

/** Database password */
define( 'DB_PASSWORD', 'yY2NdE_WHjuj7');

/** Database hostname */
define( 'DB_HOST', '127.0.0.1');

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          '<~u^mNo8i)y@>C saRbKJ3|Q,n{>E]XB)VIuxR9mi+^.jOI:$1F6;Z7?NMKL7gn>' );
define( 'SECURE_AUTH_KEY',   'gDETi-,b3_}e1anN8/yd(6 {cT5RTIP^g`]P&3-pIZ!ELM=JoSfesvDG<|zQ_9YN' );
define( 'LOGGED_IN_KEY',     '6aXw/]Q[g>ydr@,TLP3Ls36z|^f#=h*sJKz@NDRs.*td-(QV.q.3.ri/<G{)%qo+' );
define( 'NONCE_KEY',         '/I{e|2I[p8uQ>f0H#!0h.MhYEV#/a~u5po8#73;G(>|ss+`FdWybU9IequG~18<t' );
define( 'AUTH_SALT',         ' C#4Me*VB-C!?[ox>0RlsS*rUr}VU@{/iIrlq[x?e.*-TgODQ*/jWx]<Xjm`Zlp(' );
define( 'SECURE_AUTH_SALT',  '3[/%j${81f[}C__vq9~5rp0Roy%;c>k+Q2#p8B)S(E8WJ+i0j?,ZR/M69`$(BK_7' );
define( 'LOGGED_IN_SALT',    'H@uA<y)?KDm%c`nD{x[p>4[#aH8@~a:uU_&/IQ.MXyRndy;VT!nG+rG&O6<?wQOW' );
define( 'NONCE_SALT',        '>!T@CgI5?F^ja@*Y?I%c#9}oP8B_HOsfL$>2?yG)6/~k0;21(Z#2[-?L5{p<qNLM' );
define( 'WP_CACHE_KEY_SALT', 'E$qR,5T 8cJuF6vwSJpjxE#z=R#jqDhg7$5 >9J pv+X,^RP{#%=Rw&J=fu1tJOY' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpvividstg01_';

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
define( 'WP_DEBUG_LOG', false );

/* Add any custom values between this line and the "stop editing" line. */

/*Increase PHP Memory to 128MB*/


/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
