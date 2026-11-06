<?php
/**
 * Theme supports, menus, image sizes, excerpts, body classes.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $content_width ) ) {
	$content_width = 720; // Article measure (60–72 characters).
}

/**
 * Register theme features.
 */
function nn_setup() {
	load_theme_textdomain( 'narayanganj-news', NN_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 320,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'woocommerce' );
	remove_theme_support( 'core-block-patterns' );

	add_editor_style( array( 'assets/css/main.css', 'assets/css/editor.css' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Primary nav (desktop bar + mobile scroller)', 'narayanganj-news' ),
			'drawer'  => __( 'Side drawer (sub-items become an accordion)', 'narayanganj-news' ),
			'footer'  => __( 'Footer category links', 'narayanganj-news' ),
			'legal'   => __( 'Legal links (about, contact, privacy, terms)', 'narayanganj-news' ),
		)
	);

	// Editorial ratios: 3:2 for the lead, 16:9 everywhere else.
	add_image_size( 'nn-3x2', 1200, 800, true );
	add_image_size( 'nn-3x2-s', 600, 400, true );
	add_image_size( 'nn-16x9-l', 1200, 675, true );
	add_image_size( 'nn-16x9-m', 800, 450, true );
	add_image_size( 'nn-16x9-s', 400, 225, true );
}
add_action( 'after_setup_theme', 'nn_setup' );

/**
 * Short, clean auto-excerpts for card summaries.
 */
add_filter(
	'excerpt_length',
	static function () {
		return (int) apply_filters( 'nn_excerpt_words', 28 );
	},
	20
);
add_filter(
	'excerpt_more',
	static function () {
		return '…';
	},
	20
);

/**
 * Card summary: the manual excerpt, or a trimmed auto excerpt.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function nn_summary( $post ) {
	$text = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), (int) apply_filters( 'nn_excerpt_words', 28 ), '…' );
	return trim( wp_strip_all_tags( (string) apply_filters( 'nn_card_summary', $text, $post ) ) );
}

/**
 * Body classes for the open-decision settings.
 *
 * @param array $classes Classes.
 * @return array
 */
function nn_body_class( $classes ) {
	if ( ! nn_opt( 'nn_justify_mobile' ) ) {
		$classes[] = 'nn-ragged-mobile';
	}
	if ( nn_has_bold() ) {
		$classes[] = 'nn-has-bold';
	}
	if ( ! nn_images_on() ) {
		$classes[] = 'nn-text-only';
	}
	return $classes;
}
add_filter( 'body_class', 'nn_body_class' );

/**
 * Bold headlines only when the setting is on AND a licensed Bold file exists
 * (assets/fonts/SolaimanLipi-Bold.woff2, in the child or parent theme).
 *
 * @return bool
 */
function nn_has_bold() {
	return nn_opt( 'nn_use_bold' ) && file_exists( get_theme_file_path( 'assets/fonts/SolaimanLipi-Bold.woff2' ) );
}

/**
 * Add the theme's block category.
 *
 * @param array $cats Categories.
 * @return array
 */
function nn_block_category( $cats ) {
	array_unshift(
		$cats,
		array(
			'slug'  => 'nn-modules',
			'title' => __( 'সংবাদপট মডিউল (Home modules)', 'narayanganj-news' ),
			'icon'  => null,
		)
	);
	return $cats;
}
add_filter( 'block_categories_all', 'nn_block_category' );

/**
 * Bengali digits in pagination.
 *
 * @param string $html Links HTML.
 * @return string
 */
function nn_paginate_bn( $html ) {
	return preg_replace_callback(
		'/>(\d+)</',
		static function ( $m ) {
			return '>' . nn_bn( $m[1] ) . '<';
		},
		$html
	);
}
add_filter( 'paginate_links_output', 'nn_paginate_bn' );
