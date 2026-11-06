<?php
/**
 * Speed: edge-cache headers, link prefetching, lean head, Bengali page
 * language and titles, admin-bar measurements.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache headers so Cloudflare (or any CDN) can serve the page from the edge.
 * Only public, logged-out GET pages; never previews, search, 404 or password pages.
 */
function nn_edge_headers() {
	if ( ! nn_opt( 'nn_edge_cache' ) || headers_sent() ) {
		return;
	}
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';
	if ( ! in_array( $method, array( 'GET', 'HEAD' ), true ) || is_user_logged_in() || is_preview() || is_customize_preview() || is_search() || is_404() || is_feed() || is_robots() || post_password_required() ) {
		return;
	}
	foreach ( $_COOKIE as $name => $v ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( 0 === strpos( $name, 'comment_author_' ) || 0 === strpos( $name, 'wp-postpass_' ) ) {
			return;
		}
	}
	$edge    = max( 0, (int) nn_opt( 'nn_edge_ttl' ) );
	$browser = max( 0, (int) nn_opt( 'nn_browser_ttl' ) );
	if ( ! $edge ) {
		return;
	}
	header_remove( 'Cache-Control' );
	header_remove( 'Expires' );
	header_remove( 'Pragma' );
	header( sprintf( 'Cache-Control: public, max-age=%d, s-maxage=%d, stale-while-revalidate=86400, stale-if-error=86400', $browser, $edge ) );
	// Early Hints: lets Cloudflare start the font download before the HTML arrives.
	header( 'Link: <' . esc_url_raw( NN_URI . '/assets/fonts/SolaimanLipi.woff2' ) . '>; rel=preload; as=font; type="font/woff2"; crossorigin', false );
}
add_action( 'template_redirect', 'nn_edge_headers', 99 );

/**
 * Faster page-to-page navigation: prefetch links on hover/touch with the
 * browser's speculation rules (WordPress 6.8+ configuration, else our own tag).
 *
 * @param array|null $config Core configuration.
 * @return array
 */
function nn_speculation_config( $config ) {
	return array(
		'mode'      => 'prefetch',
		'eagerness' => 'moderate',
	);
}
add_filter( 'wp_speculation_rules_configuration', 'nn_speculation_config' );

/**
 * Own speculation rules for WordPress older than 6.8.
 */
function nn_speculation_fallback() {
	if ( function_exists( 'wp_get_speculation_rules' ) || is_user_logged_in() ) {
		return;
	}
	$rules = array(
		'prefetch' => array(
			array(
				'where'     => array(
					'and' => array(
						array( 'href_matches' => '/*' ),
						array(
							'not' => array(
								'href_matches' => array( '/wp-admin/*', '/wp-login.php', '/wp-json/*', '/*?*(^|&)s=*', '/*?*(^|&)preview=*' ),
							),
						),
					),
				),
				'eagerness' => 'moderate',
			),
		),
	);
	echo '<script type="speculationrules">' . wp_json_encode( $rules ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON.
}
add_action( 'wp_footer', 'nn_speculation_fallback', 20 );

/**
 * Messenger share link: the web dialog when a Facebook App ID is set
 * (works on desktop and phone), otherwise the app deep link (phones).
 *
 * @param string $url Page URL.
 * @return string
 */
function nn_messenger_url( $url ) {
	$app = preg_replace( '/\D/', '', (string) nn_opt( 'nn_fb_app_id' ) );
	if ( $app ) {
		return 'https://www.facebook.com/dialog/send?app_id=' . $app . '&link=' . rawurlencode( $url ) . '&redirect_uri=' . rawurlencode( $url );
	}
	return 'fb-messenger://share/?link=' . rawurlencode( $url );
}

/**
 * Lean head: no global-styles dump, no SVG filter block, no extra block CSS.
 */
function nn_lean_assets() {
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'wp-block-library-theme' );
}
add_action( 'wp_enqueue_scripts', 'nn_lean_assets', 100 );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );

/**
 * The visible page language is Bengali.
 *
 * @param string $output Attributes.
 * @return string
 */
function nn_language_attributes( $output ) {
	if ( is_admin() ) {
		return $output;
	}
	$output = preg_replace( '/lang="[^"]*"/', 'lang="bn-BD"', $output );
	if ( false === strpos( $output, 'lang=' ) ) {
		$output .= ' lang="bn-BD"';
	}
	return $output;
}
add_filter( 'language_attributes', 'nn_language_attributes' );

/**
 * Browser-tab titles in Bengali for the pages WordPress words in English.
 *
 * @param array $parts Title parts.
 * @return array
 */
function nn_title_parts( $parts ) {
	if ( is_404() ) {
		$parts['title'] = 'পাতাটি পাওয়া যায়নি';
	} elseif ( is_search() ) {
		$parts['title'] = 'খোঁজার ফল: ' . get_search_query();
	}
	if ( ! empty( $parts['page'] ) ) {
		global $page, $paged;
		$n             = max( (int) $paged, (int) $page );
		$parts['page'] = $n > 1 ? 'পাতা ' . nn_bn( $n ) : '';
	}
	return $parts;
}
add_filter( 'document_title_parts', 'nn_title_parts' );

/**
 * Meta description and social tags, unless an SEO plugin already prints them.
 */
function nn_meta_tags() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) ) {
		return;
	}
	$desc = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && ! post_password_required( $post ) ) {
			$desc = nn_summary( $post );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$desc = wp_strip_all_tags( term_description() );
	} elseif ( is_front_page() ) {
		$desc = get_bloginfo( 'description' );
	}
	$desc = trim( preg_replace( '/\s+/u', ' ', (string) $desc ) );
	if ( '' === $desc ) {
		return;
	}
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:locale" content="bn_BD">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( is_singular() && has_post_thumbnail() ) {
		$img = wp_get_attachment_image_url( get_post_thumbnail_id(), 'nn-16x9-l' );
		if ( $img ) {
			echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
			echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		}
	}
}
add_action( 'wp_head', 'nn_meta_tags', 4 );

/**
 * Admin bar: queries · milliseconds · peak memory for the current page.
 *
 * @param WP_Admin_Bar $bar Admin bar.
 */
function nn_admin_bar_stats( $bar ) {
	if ( is_admin() || ! nn_opt( 'nn_admin_stats' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$bar->add_node(
		array(
			'id'    => 'nn-stats',
			'title' => sprintf(
				'%d queries · %d ms · %.1f MB',
				get_num_queries(),
				(int) round( timer_stop( 0, 4 ) * 1000 ),
				memory_get_peak_usage( true ) / 1048576
			),
			'href'  => admin_url( 'admin.php?page=nn-settings' ),
			'meta'  => array( 'title' => 'Measured when the admin bar was built. Targets: under 30 queries, 150 ms, 20 MB.' ),
		)
	);
}
add_action( 'admin_bar_menu', 'nn_admin_bar_stats', 9999 );
