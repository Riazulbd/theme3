<?php
/**
 * Light / dark mode (automatic by the visitor's local time, with a manual
 * button) and the header colours picked in থিম সেটিংস.
 *
 * The mode is decided in the browser by a tiny inline script, so it works
 * behind a full-page cache and never flashes the wrong colours.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline script in <head>: sets <html data-theme>. A manual choice lasts until
 * the next automatic switch (day start or night start), then auto takes over.
 */
function nn_mode_script() {
	$mode  = (string) nn_opt( 'nn_mode_default' );
	$day   = max( 0, min( 23, (int) nn_opt( 'nn_day_start' ) ) );
	$night = max( 0, min( 23, (int) nn_opt( 'nn_night_start' ) ) );
	$js    = '(function(){var d=document.documentElement,M=' . wp_json_encode( $mode ) . ',A=' . $day . ',B=' . $night . ',K="nn-mode";'
		. 'function auto(){if(M==="light"||M==="dark"){return M}var h=new Date().getHours();return(A<B?(h>=A&&h<B):(h>=A||h<B))?"light":"dark"}'
		. 'function next(){var n=new Date(),r=[];[A,B].forEach(function(x){var t=new Date(n);t.setHours(x,0,0,0);if(t<=n){t.setDate(t.getDate()+1)}r.push(t.getTime())});return Math.min(r[0],r[1])}'
		. 'function apply(m){d.setAttribute("data-theme",m);d.style.colorScheme=m}'
		. 'var m=null;try{var s=(localStorage.getItem(K)||"").split(":");if((s[0]==="light"||s[0]==="dark")&&+s[1]>Date.now()){m=s[0]}}catch(e){}'
		. 'apply(m||auto());'
		. 'window.nnSetMode=function(x){if(x!=="light"&&x!=="dark"){x=d.getAttribute("data-theme")==="dark"?"light":"dark"}apply(x);try{localStorage.setItem(K,x+":"+next())}catch(e){}return x};'
		. '})();';
	echo '<script>' . $js . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- built from integers and a JSON-encoded whitelist value.
}
add_action( 'wp_head', 'nn_mode_script', 1 );

/**
 * Readable text colour for a background: dark text on light, light text on dark.
 *
 * @param string $hex Background colour (#rrggbb).
 * @return string
 */
function nn_contrast_text( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return '#0B0B12';
	}
	$lin = static function ( $c ) {
		$c = hexdec( $c ) / 255;
		return $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
	};
	$l = 0.2126 * $lin( substr( $hex, 0, 2 ) ) + 0.7152 * $lin( substr( $hex, 2, 2 ) ) + 0.0722 * $lin( substr( $hex, 4, 2 ) );
	return $l > 0.35 ? '#0B0B12' : '#F2F2F7';
}

/**
 * Background + text pair for one mode. A missing text colour is chosen for
 * contrast with the background (white header in dark mode gets dark text).
 *
 * @param string $bg_key  Setting key of the background.
 * @param string $fg_key  Setting key of the text colour.
 * @param string $def_bg  Default background.
 * @return array|null [bg, fg] or null when neither is set.
 */
function nn_colour_pair( $bg_key, $fg_key, $def_bg ) {
	$bg = sanitize_hex_color( (string) nn_opt( $bg_key ) );
	$fg = sanitize_hex_color( (string) nn_opt( $fg_key ) );
	if ( ! $bg && ! $fg ) {
		return null;
	}
	$bg = $bg ? $bg : $def_bg;
	$fg = $fg ? $fg : nn_contrast_text( $bg );
	return array( $bg, $fg );
}

/**
 * Colour-scheme meta, card size, header and footer colour variables.
 */
function nn_header_colours() {
	echo '<meta name="color-scheme" content="light dark">' . "\n";
	$fs   = max( 16, min( 34, (int) nn_opt( 'nn_card_font' ) ) );
	$vars = ':root{--fs-card:' . $fs . 'px;--lh-card:' . (int) round( $fs * 1.5 ) . 'px}';

	$groups = array(
		'hdr' => array( array( 'nn_hdr_bg', 'nn_hdr_text', '#FFFFFF' ), array( 'nn_hdr_bg_dark', 'nn_hdr_text_dark', '#0E0E14' ) ),
		'ftr' => array( array( 'nn_ftr_bg', 'nn_ftr_text', '#F7F7FA' ), array( 'nn_ftr_bg', 'nn_ftr_text', '#171722' ) ),
	);
	foreach ( $groups as $g => $modes ) {
		$light = nn_colour_pair( $modes[0][0], $modes[0][1], $modes[0][2] );
		$dark  = nn_colour_pair( $modes[1][0], $modes[1][1], $modes[1][2] );
		if ( ! $light && ! $dark ) {
			continue;
		}
		$light = $light ? $light : array( $modes[0][2], nn_contrast_text( $modes[0][2] ) );
		$dark  = $dark ? $dark : array( $modes[1][2], nn_contrast_text( $modes[1][2] ) );
		$vars .= ':root{--' . $g . '-bg:' . $light[0] . ';--' . $g . '-fg:' . $light[1] . '}';
		$vars .= ':root[data-theme="dark"]{--' . $g . '-bg:' . $dark[0] . ';--' . $g . '-fg:' . $dark[1] . '}';
	}
	echo '<style id="nn-vars">' . $vars . "</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- integers and validated hex colours.
}
add_action( 'wp_head', 'nn_header_colours', 3 );

/**
 * Body class when any header colour is set (CSS remaps the header's tokens).
 *
 * @param array $classes Classes.
 * @return array
 */
function nn_hdr_body_class( $classes ) {
	if ( sanitize_hex_color( (string) nn_opt( 'nn_ftr_bg' ) ) || sanitize_hex_color( (string) nn_opt( 'nn_ftr_text' ) ) ) {
		$classes[] = 'nn-ftr-custom';
	}
	foreach ( array( 'nn_hdr_bg', 'nn_hdr_text', 'nn_hdr_bg_dark', 'nn_hdr_text_dark' ) as $k ) {
		if ( sanitize_hex_color( (string) nn_opt( $k ) ) ) {
			$classes[] = 'nn-hdr-custom';
			break;
		}
	}
	return $classes;
}
add_filter( 'body_class', 'nn_hdr_body_class' );

/**
 * The mode switch button: visible on mobile, next to search.
 *
 * @return string
 */
function nn_mode_button() {
	return '<button class="nn-iconbtn nn-modebtn" type="button" data-nn-mode aria-label="' . esc_attr__( 'ডার্ক / লাইট মোড বদলান', 'narayanganj-news' ) . '">'
		. nn_icon( 'moon', 'nn-modebtn__moon' ) . nn_icon( 'sun', 'nn-modebtn__sun' ) . '</button>';
}
