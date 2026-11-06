<?php
/**
 * Front-end and editor assets.
 *
 * - main.css is inlined in <head> (about 7 KB gzipped): no render-blocking request.
 * - SolaimanLipi (Bengali) is preloaded; Inter (Latin letters only) loads on demand via unicode-range.
 * - One small deferred script, no jQuery.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minified main.css with absolute font URLs, ready to inline.
 *
 * @return string
 */
function nn_inline_css() {
	$file = NN_DIR . '/assets/css/main.css';
	if ( ! is_readable( $file ) ) {
		return '';
	}
	$css = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	$css = str_replace( 'url(../fonts/', 'url(' . NN_URI . '/assets/fonts/', $css );
	$css = preg_replace( '#/\*.*?\*/#s', '', $css );
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\s*([{};,>])\s*/', '$1', $css );
	return trim( $css );
}

/**
 * Enqueue styles and scripts.
 */
function nn_enqueue() {
	if ( apply_filters( 'nn_inline_css', true ) ) {
		wp_register_style( 'nn-main', false, array(), NN_VERSION );
		wp_enqueue_style( 'nn-main' );
		wp_add_inline_style( 'nn-main', nn_inline_css() );
	} else {
		wp_enqueue_style( 'nn-main', NN_URI . '/assets/css/main.css', array(), NN_VERSION );
	}

	if ( nn_has_bold() ) {
		wp_add_inline_style(
			'nn-main',
			'@font-face{font-family:"SolaimanLipi";src:url(' . esc_url( get_theme_file_uri( 'assets/fonts/SolaimanLipi-Bold.woff2' ) ) . ') format("woff2");font-weight:700;font-style:normal;font-display:swap}'
		);
	}

	// Child theme stylesheet, if any.
	if ( is_child_theme() ) {
		wp_enqueue_style( 'nn-child', get_stylesheet_uri(), array( 'nn-main' ), wp_get_theme()->get( 'Version' ) );
	}

	wp_enqueue_script(
		'nn-theme',
		NN_URI . '/assets/js/theme.js',
		array(),
		NN_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_singular( 'post' ) && nn_opt( 'nn_card_on' ) ) {
		wp_enqueue_script( 'nn-card', NN_URI . '/assets/js/card.js', array(), NN_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}

	$data = array(
		'searchUrl' => esc_url_raw( rest_url( 'wp/v2/search' ) ),
		'i18n'      => array(
			'suggest'   => 'পরামর্শ',
			'searching' => 'খোঁজা হচ্ছে…',
			'none'      => 'কিছু পাওয়া যায়নি',
			'copied'    => 'লিংক কপি হয়েছে',
		),
	);
	if ( is_singular( 'post' ) && nn_opt( 'nn_track_views' ) ) {
		$data['viewUrl'] = esc_url_raw( rest_url( 'nn/v1/view/' . get_queried_object_id() ) );
		$data['postId']  = get_queried_object_id();
	}
	wp_add_inline_script( 'nn-theme', 'window.nnData=' . wp_json_encode( apply_filters( 'nn_script_data', $data ) ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'nn_enqueue' );

/**
 * Preload the Bengali font (used by every page, above the fold).
 */
function nn_preload_fonts() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( NN_URI . '/assets/fonts/SolaimanLipi.woff2' )
	);
}
add_action( 'wp_head', 'nn_preload_fonts', 2 );

/**
 * Editor script: module blocks, article settings panel, video/embed blocks removed.
 */
function nn_editor_assets() {
	wp_enqueue_script(
		'nn-editor',
		NN_URI . '/assets/js/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data', 'wp-core-data', 'wp-plugins', 'wp-editor', 'wp-html-entities', 'wp-dom-ready' ),
		NN_VERSION,
		true
	);
	wp_add_inline_script(
		'nn-editor',
		'window.nnEditor=' . wp_json_encode(
			array(
				'blocks'     => nn_block_defs_for_js(),
				'disallowed' => apply_filters( 'nn_disallowed_blocks', array( 'core/video', 'core/embed' ) ),
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'nn_editor_assets' );
