<?php
/**
 * Performance trims and the editorial rules from the brief:
 * no author profiles, no comments, no video or embed players.
 * Every rule can be switched off with a filter (see README.md).
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove head clutter and unused front-end assets.
 */
function nn_cleanup_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'nn_cleanup_head' );

/**
 * Load only the CSS of core blocks that are on the page.
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * Drop small unused styles.
 */
function nn_dequeue() {
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_script( 'wp-embed' );
	if ( apply_filters( 'nn_disable_comments', true ) ) {
		wp_dequeue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'nn_dequeue', 100 );

/**
 * No author profiles: author archives go to the home page, users leave the sitemap.
 */
function nn_no_author_archives() {
	if ( is_author() && apply_filters( 'nn_disable_author_archives', true ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'nn_no_author_archives' );

add_filter(
	'wp_sitemaps_add_provider',
	static function ( $provider, $name ) {
		return ( 'users' === $name && apply_filters( 'nn_disable_author_archives', true ) ) ? false : $provider;
	},
	10,
	2
);

/**
 * No comments on the article page (the design has none).
 */
if ( apply_filters( 'nn_disable_comments', true ) ) {
	add_filter( 'comments_open', '__return_false', 20 );
	add_filter( 'pings_open', '__return_false', 20 );
	add_filter( 'comments_array', '__return_empty_array', 20 );
}

/**
 * Smaller images: new uploads get WebP sub-sizes when the server supports it.
 * Turn off with add_filter( 'nn_convert_uploads_to_webp', '__return_false' ).
 *
 * @param array $formats Map of mime => output mime.
 * @return array
 */
function nn_webp_output( $formats ) {
	if ( ! apply_filters( 'nn_convert_uploads_to_webp', true ) ) {
		return $formats;
	}
	if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		return $formats;
	}
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
}
add_filter( 'image_editor_output_format', 'nn_webp_output' );
