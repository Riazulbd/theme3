<?php
/**
 * Narayanganj News: theme bootstrap.
 *
 * Every feature lives in a small file under /inc. Child themes can override
 * any template or template part, and change behaviour through the filters
 * and actions listed in README.md.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

define( 'NN_VERSION', '1.1.13' );
define( 'NN_DIR', get_template_directory() );
define( 'NN_URI', get_template_directory_uri() );

$nn_includes = array(
	'helpers',    // Options, Bengali digits/date, icons, wordmark, image helpers.
	'setup',      // Theme supports, menus, image sizes, excerpt.
	'cache',      // Fragment cache with stale-while-revalidate.
	'admin',      // Settings screen in the admin menu (one small option).
	'modes',      // Light/dark mode and header colours.
	'perf',       // Edge headers, prefetch, Bengali lang/titles, admin-bar stats.
	'assets',     // Inline CSS, fonts preload, deferred JS.
	'cleanup',    // Performance and editorial rules (no authors, no comments, no video blocks).
	'meta',       // Per-article settings (text-only, source line).
	'menus',      // Menu trees with category fallback.
	'cards',      // Story card atom.
	'modules',    // M03–M09 module renderers.
	'blocks',     // Block registration for the module library.
	'ticker',     // Scrolling news ticker.
	'views',      // Most-read counter.
	'patterns',   // Home page patterns.
);

foreach ( $nn_includes as $nn_file ) {
	require_once NN_DIR . '/inc/' . $nn_file . '.php';
}
unset( $nn_includes, $nn_file );
