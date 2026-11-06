<?php
/**
 * Shared helpers: options, Bengali digits and date, icons, wordmark, images.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for every theme setting (admin menu → থিম সেটিংস).
 *
 * @return array
 */
function nn_defaults() {
	static $d = null;
	if ( null === $d ) {
		$d = array(
			'nn_brand_name'         => '',
			'nn_about_text'         => '{site} একটি বাংলা সংবাদমাধ্যম। দেশ ও বিদেশের গুরুত্বপূর্ণ খবর যাচাই করে সহজ ভাষায় পাঠকের কাছে পৌঁছে দেওয়াই আমাদের কাজ।',
			'nn_show_date_line'     => false,
			'nn_image_mode'         => 'photos',
			'nn_justify_mobile'     => true,
			'nn_use_bold'           => false,
			'nn_track_views'        => true,
			'nn_most_read_days'     => 7,
			'nn_image_placeholders' => true,
			'nn_home_cats'          => array(),
			'nn_logo'               => 0,
			'nn_footer_logo'        => 0,
			'nn_ftr_bg'             => '',
			'nn_ftr_text'           => '',
			'nn_card_font'          => 22,
			'nn_fb_app_id'          => '',
			'nn_card_on'            => true,
			'nn_card_style'         => 'classic',
			'nn_card_color'         => '#B3121C',
			'nn_card_head'          => '',
			'nn_card_head_text'     => '',
			'nn_card_text'          => '',
			'nn_card_bar'           => '',
			'nn_card_bar_text'      => '',
			'nn_card_footer'        => '',
			'nn_lead_cat'           => 0,
			'nn_top_cat'            => 0,
			'nn_footer_name'        => '',
			'nn_contact'            => '',
			'nn_follow_title'       => 'ফলো করুন',
			'nn_app_title'          => 'মোবাইল অ্যাপ ডাউনলোড করুন',
			'nn_app_android'        => '',
			'nn_app_ios'            => '',
			'nn_copyright'          => '© {year} {site}। সর্বস্বত্ব সংরক্ষিত।',
			'nn_social_youtube'     => '',
			'nn_social_instagram'   => '',
			'nn_social_telegram'    => '',
			'nn_social_facebook'    => '',
			'nn_social_x'           => '',
			'nn_social_whatsapp'    => '',
			// Colours and modes.
			'nn_mode_default'       => 'auto',
			'nn_day_start'          => 6,
			'nn_night_start'        => 18,
			'nn_hdr_bg'             => '',
			'nn_hdr_text'           => '',
			'nn_hdr_bg_dark'        => '',
			'nn_hdr_text_dark'      => '',
			// Ticker.
			'nn_ticker_on'          => true,
			'nn_ticker_label'       => 'শিরোনাম',
			'nn_ticker_count'       => 10,
			'nn_ticker_speed'       => 60,
			'nn_ticker_cat'         => 0,
			// Performance.
			'nn_cache_ttl'          => 300,
			'nn_edge_cache'         => true,
			'nn_edge_ttl'           => 300,
			'nn_browser_ttl'        => 60,
			'nn_admin_stats'        => true,
		);
		$d = apply_filters( 'nn_defaults', $d );
	}
	return $d;
}

/**
 * All saved settings: one small option, read once per request.
 *
 * @return array
 */
function nn_settings() {
	static $s = null;
	if ( null === $s ) {
		$s = get_option( 'nn_settings', array() );
		if ( ! is_array( $s ) ) {
			$s = array();
		}
	}
	return $s;
}

/**
 * Read a theme setting with its default.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function nn_opt( $key ) {
	$defaults = nn_defaults();
	$saved    = nn_settings();
	if ( array_key_exists( $key, $saved ) ) {
		$value = $saved[ $key ];
	} else {
		$value = isset( $defaults[ $key ] ) ? $defaults[ $key ] : null;
	}
	return apply_filters( 'nn_option_' . $key, $value );
}

/**
 * Convert Latin digits to Bengali digits.
 *
 * @param string|int $value Value.
 * @return string
 */
function nn_bn( $value ) {
	return strtr(
		(string) $value,
		array(
			'0' => '০',
			'1' => '১',
			'2' => '২',
			'3' => '৩',
			'4' => '৪',
			'5' => '৫',
			'6' => '৬',
			'7' => '৭',
			'8' => '৮',
			'9' => '৯',
		)
	);
}

/**
 * Site name shown in the wordmark, footer and copyright (setting, not hard-coded).
 *
 * @return string
 */
function nn_brand() {
	$name = trim( (string) nn_opt( 'nn_brand_name' ) );
	if ( '' === $name ) {
		$name = get_bloginfo( 'name' );
	}
	return apply_filters( 'nn_brand_name', $name );
}

/**
 * Whether photos are shown. Module/article override first, then the site default.
 *
 * @param string $mode 'on', 'off' or '' / 'default'.
 * @return bool
 */
function nn_images_on( $mode = '' ) {
	if ( 'on' === $mode ) {
		$on = true;
	} elseif ( 'off' === $mode ) {
		$on = false;
	} else {
		$on = 'text' !== nn_opt( 'nn_image_mode' );
	}
	return (bool) apply_filters( 'nn_images_on', $on, $mode );
}

/**
 * Today's date in Bengali for the optional masthead date line.
 * This is the newspaper date line, never a story date.
 *
 * @return string
 */
function nn_date_line() {
	$days   = array( 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার' );
	$months = array( 1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
	$line   = sprintf(
		'%1$s, %2$s %3$s %4$s',
		$days[ (int) wp_date( 'w' ) ],
		nn_bn( wp_date( 'j' ) ),
		$months[ (int) wp_date( 'n' ) ],
		nn_bn( wp_date( 'Y' ) )
	);
	return apply_filters( 'nn_date_line', $line );
}

/**
 * A date in Bengali, e.g. "০২ নভেম্বর ২০২৬".
 *
 * @param int $ts Unix timestamp.
 * @return string
 */
function nn_bn_date( $ts ) {
	$months = array( 1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
	return nn_bn( wp_date( 'd', $ts ) ) . ' ' . $months[ (int) wp_date( 'n', $ts ) ] . ' ' . nn_bn( wp_date( 'Y', $ts ) );
}

/**
 * Long Bengali date, e.g. "বৃহস্পতিবার, ৫ নভেম্বর, ২০২৬".
 *
 * @param int $ts Unix timestamp.
 * @return string
 */
function nn_bn_date_long( $ts ) {
	$days   = array( 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার' );
	$months = array( 1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
	return $days[ (int) wp_date( 'w', $ts ) ] . ', ' . nn_bn( wp_date( 'j', $ts ) ) . ' ' . $months[ (int) wp_date( 'n', $ts ) ] . ', ' . nn_bn( wp_date( 'Y', $ts ) );
}

/**
 * Share-picture styles: key => admin label.
 *
 * @return array
 */
function nn_card_styles() {
	return array(
		'classic' => 'Classic: top band with logo and date, framed photo, headline on colour',
		'dark'    => 'Dark: photo fading into dark, date tag, site-name ribbon, headline, links row',
		'light'   => 'Light: photo on top, white panel with headline (last line in colour)',
		'band'    => 'Colour band: photo on top, logo on photo, headline on a colour band, date and website',
		'strip'   => 'Wide strip: landscape photo with a logo strip (no headline)',
	);
}

/**
 * Everything the picture needs. Colours left empty in the settings are
 * worked out per style in card.js.
 *
 * @param WP_Post|null $post Post, or null for the settings preview.
 * @return array
 */
function nn_card_data( $post = null ) {
	$logo_id = (int) nn_opt( 'nn_logo' );
	if ( ! $logo_id ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
	}
	$foot = trim( (string) nn_opt( 'nn_card_footer' ) );
	$main = sanitize_hex_color( (string) nn_opt( 'nn_card_color' ) );
	$pick = static function ( $key ) {
		$c = sanitize_hex_color( (string) nn_opt( $key ) );
		return $c ? $c : '';
	};
	$style = (string) nn_opt( 'nn_card_style' );
	$ts    = $post ? (int) get_post_timestamp( $post ) : time();
	$host  = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	return array(
		'style'    => isset( nn_card_styles()[ $style ] ) ? $style : 'classic',
		'head'     => $pick( 'nn_card_head' ),
		'headText' => $pick( 'nn_card_head_text' ),
		'text'     => $pick( 'nn_card_text' ),
		'bar'      => $pick( 'nn_card_bar' ),
		'barText'  => $pick( 'nn_card_bar_text' ),
		'color'    => $main ? $main : '#B3121C',
		'title'    => $post ? wp_strip_all_tags( get_the_title( $post ) ) : 'নমুনা শিরোনাম: খবরের শিরোনাম এখানে এভাবে দেখাবে, দুই লাইনে',
		'date'     => nn_bn_date( $ts ),
		'dateLong' => nn_bn_date_long( $ts ),
		'img'      => ( $post && has_post_thumbnail( $post ) ) ? (string) get_the_post_thumbnail_url( $post, 'nn-16x9-l' ) : '',
		'logo'     => $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'full' ) : '',
		'site'     => nn_brand(),
		'host'     => $host,
		'foot'     => '' === $foot ? 'বিস্তারিত কমেন্টে' : $foot,
		'file'     => $post ? 'news-' . (int) $post->ID : 'preview',
		'busy'     => 'তৈরি হচ্ছে…',
	);
}

/**
 * "Download image" / "Share image" buttons for the article page. The picture
 * itself is drawn in the browser by assets/js/card.js from this data.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function nn_card_bar( $post ) {
	if ( ! nn_opt( 'nn_card_on' ) ) {
		return '';
	}
	return '<div class="nn-cardbar" data-nn-card="' . esc_attr( wp_json_encode( nn_card_data( $post ) ) ) . '">'
		. '<button class="nn-cardbtn" type="button" data-act="download">' . esc_html__( 'ছবি ডাউনলোড', 'narayanganj-news' ) . '</button>'
		. '<button class="nn-cardbtn" type="button" data-act="share" hidden>' . esc_html__( 'ছবি শেয়ার', 'narayanganj-news' ) . '</button>'
		. '</div>';
}

/**
 * Inline SVG icon: line style, 1.5px stroke, 24px grid.
 *
 * @param string $name  Icon name.
 * @param string $class Extra class.
 * @return string
 */
function nn_icon( $name, $class = '' ) {
	$paths = array(
		'menu'          => 'M4 7H20M4 12H20M4 17H20',
		'close'         => 'M6 6L18 18M18 6L6 18',
		'search'        => 'M10.5 4A6.5 6.5 0 1 0 10.5 17A6.5 6.5 0 1 0 10.5 4ZM15.5 15.5L20 20',
		'chevron-down'  => 'M6 9L12 15L18 9',
		'chevron-right' => 'M9 6L15 12L9 18',
		'arrow-up'      => 'M12 19V5M6 11L12 5L18 11',
		'link'          => 'M10 14A4 4 0 0 0 15.66 14L18.66 11A4 4 0 0 0 13 5.34L12 6.34M14 10A4 4 0 0 0 8.34 10L5.34 13A4 4 0 0 0 11 18.66L12 17.66',
		'facebook'      => 'M15.5 4H13.5A3.5 3.5 0 0 0 10 7.5V20M7 11H14.5',
		'x'             => 'M4.5 4L15.5 20H19.5L8.5 4ZM19 4L13.4 10.4M10.6 13.6L5 20',
		'whatsapp'      => 'M12 3.5A8.5 8.5 0 0 0 4.7 16.4L3.5 20.5L7.7 19.3A8.5 8.5 0 1 0 12 3.5ZM9.2 8.2C9.2 11.8 12.2 14.8 15.8 14.8L16.6 13.2L14.6 12.2L13.6 13.1A5 5 0 0 1 10.9 10.4L11.8 9.4L10.8 7.4Z',
		'check'         => 'M5 12.5L10 17L19 7',
		'messenger'     => 'M12 3A9 9 0 0 0 3 11.7C3 14.4 4.2 16.8 6.1 18.4V21L8.8 19.5A9.5 9.5 0 0 0 12 20.4A8.7 8.7 0 0 0 12 3ZM6.5 13.8L10.5 9.5L13 12L17.5 9.5L13.5 13.8L11 11.2Z',
		'telegram'      => 'M21 4L3 11L9 13.5L11.5 20L14.5 15.5L19 19L21 4ZM9 13.5L21 4',
		'youtube'       => 'M3 8A3 3 0 0 1 6 5H18A3 3 0 0 1 21 8V16A3 3 0 0 1 18 19H6A3 3 0 0 1 3 16ZM10 9.5L15 12L10 14.5Z',
		'instagram'     => 'M7 3H17A4 4 0 0 1 21 7V17A4 4 0 0 1 17 21H7A4 4 0 0 1 3 17V7A4 4 0 0 1 7 3ZM12 8A4 4 0 1 0 12 16A4 4 0 1 0 12 8ZM17 6.9V7',
		'moon'          => 'M20 14.5A8 8 0 1 1 9.5 4A6.5 6.5 0 0 0 20 14.5Z',
		'sun'           => 'M12 8A4 4 0 1 0 12 16A4 4 0 1 0 12 8ZM12 2.5V4.5M12 19.5V21.5M2.5 12H4.5M19.5 12H21.5M5.3 5.3L6.7 6.7M17.3 17.3L18.7 18.7M5.3 18.7L6.7 17.3M17.3 6.7L18.7 5.3',
		'pause'         => 'M9 6V18M15 6V18',
		'play'          => 'M8 5.5L18 12L8 18.5Z',
	);
	$paths = apply_filters( 'nn_icon_paths', $paths );
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="nn-icon' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="' . esc_attr( $paths[ $name ] ) . '"/></svg>';
}

/**
 * Wordmark: the uploaded logo (Appearance → Customize → Site Identity is not
 * needed: use the logo field in থিম সেটিংস or the core custom logo) or the site
 * name with the brand rule.
 *
 * @param string $size 'l', 'm', 's' or 'auto'.
 * @param bool   $link Link to the home page.
 * @param bool|string $logo true/'header' = header logo, 'footer' = footer logo, false = text.
 * @return string
 */
function nn_wordmark( $size = 'auto', $link = true, $logo = false ) {
	if ( $logo ) {
		$id = (int) nn_opt( 'footer' === $logo ? 'nn_footer_logo' : 'nn_logo' );
		if ( ! $id && 'footer' !== $logo ) {
			$id = (int) get_theme_mod( 'custom_logo' );
		}
		if ( $id ) {
			$img = wp_get_attachment_image(
				$id,
				'full',
				false,
				array(
					'class'         => 'nn-logo__img',
					'alt'           => nn_brand(),
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
					'sizes'         => '200px',
				)
			);
			if ( $img ) {
				return '<a class="nn-wordmark nn-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . $img . '</a>';
			}
		}
		if ( 'only' === $logo ) {
			return ''; // No logo uploaded: show nothing rather than the site name.
		}
	}
	$cls  = 'nn-wordmark nn-wordmark--' . sanitize_html_class( $size );
	$text = '<span class="nn-wordmark__text">' . esc_html( nn_brand() ) . '</span>';
	if ( $link ) {
		return '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . $text . '</a>';
	}
	return '<span class="' . esc_attr( $cls ) . '">' . $text . '</span>';
}

/**
 * Build a `sizes` attribute from the desktop slot width and the tablet value.
 *
 * @param int    $desktop Desktop slot width in px.
 * @param string $tablet  Tablet value (CSS length).
 * @param string $mobile  Mobile value (CSS length).
 * @return string
 */
function nn_sizes( $desktop, $tablet = 'calc(100vw - 64px)', $mobile = 'calc(100vw - 40px)' ) {
	return sprintf( '(min-width: 1024px) %dpx, (min-width: 600px) %s, %s', (int) $desktop, $tablet, $mobile );
}

/**
 * Responsive featured image (srcset/sizes from WordPress, native lazy loading).
 *
 * @param WP_Post $post  Post.
 * @param string  $ratio '3x2' or '16x9'.
 * @param string  $sizes Sizes attribute.
 * @param bool    $eager Above-the-fold image (eager + fetchpriority high).
 * @return string
 */
function nn_image( $post, $ratio = '16x9', $sizes = '', $eager = false ) {
	$id = get_post_thumbnail_id( $post );
	if ( ! $id ) {
		if ( nn_opt( 'nn_image_placeholders' ) ) {
			return '<span class="nn-ph" aria-hidden="true"></span>';
		}
		return '';
	}
	$size = ( '3x2' === $ratio ) ? 'nn-3x2' : 'nn-16x9-m';
	$attr = array(
		'class'    => 'nn-img',
		'decoding' => 'async',
		'loading'  => $eager ? 'eager' : 'lazy',
	);
	if ( $sizes ) {
		$attr['sizes'] = $sizes;
	}
	if ( $eager ) {
		$attr['fetchpriority'] = 'high';
	}
	$attr = apply_filters( 'nn_image_attributes', $attr, $post, $ratio );
	return wp_get_attachment_image( $id, apply_filters( 'nn_image_size', $size, $ratio, $post ), false, $attr );
}

/**
 * Per-breakpoint visibility classes for list item $i.
 *
 * @param int   $i   Zero-based index.
 * @param int   $n   Total items.
 * @param array $vis Visible counts per breakpoint: d (desktop), t (tablet), m (mobile).
 * @return string
 */
function nn_vis( $i, $n, $vis ) {
	$out = array();
	foreach ( array( 'd', 't', 'm' ) as $bp ) {
		$show = isset( $vis[ $bp ] ) ? min( (int) $vis[ $bp ], $n ) : $n;
		if ( $i >= $show ) {
			$out[] = 'hide-' . $bp;
		} elseif ( $i === $show - 1 ) {
			$out[] = 'last-' . $bp;
		}
	}
	return implode( ' ', $out );
}

/**
 * True while WordPress renders a block preview in the editor.
 *
 * @return bool
 */
function nn_is_editor_preview() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * Social profile links (only the ones that are set).
 *
 * @return array name => url
 */
function nn_social_links() {
	$out = array();
	foreach ( array( 'facebook', 'x', 'whatsapp', 'youtube', 'instagram', 'telegram' ) as $net ) {
		$url = trim( (string) nn_opt( 'nn_social_' . $net ) );
		if ( '' !== $url ) {
			$out[ $net ] = $url;
		}
	}
	return apply_filters( 'nn_social_links', $out );
}

/**
 * Social icon buttons.
 *
 * @return string
 */
function nn_social_html() {
	$labels = array(
		'facebook' => 'ফেসবুক',
		'x'        => 'এক্স',
		'whatsapp' => 'হোয়াটসঅ্যাপ',
		'youtube'  => 'ইউটিউব',
		'instagram' => 'ইনস্টাগ্রাম',
		'telegram' => 'টেলিগ্রাম',
	);
	$links  = nn_social_links();
	if ( ! $links ) {
		return '';
	}
	$h = '<ul class="nn-social">';
	foreach ( $links as $net => $url ) {
		$h .= '<li><a class="nn-iconbtn nn-brand nn-brand--' . esc_attr( $net ) . '" href="' . esc_url( $url ) . '" rel="noopener" aria-label="' . esc_attr( isset( $labels[ $net ] ) ? $labels[ $net ] : $net ) . '">' . nn_icon( $net ) . '</a></li>';
	}
	return $h . '</ul>';
}

/**
 * Search form used in the header, the drawer, the search sheet and searchform.php.
 *
 * @param string $id Unique input id.
 * @return string
 */
function nn_search_form( $id = 'nn-s' ) {
	$h  = '<form class="nn-field" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">';
	$h .= '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html__( 'খুঁজুন', 'narayanganj-news' ) . '</label>';
	$h .= '<span class="nn-field__icon">' . nn_icon( 'search' ) . '</span>';
	$h .= '<input class="nn-field__input" id="' . esc_attr( $id ) . '" type="search" name="s" value="' . esc_attr( get_search_query() ) . '" placeholder="' . esc_attr__( 'খবর, বিষয় বা জায়গা খুঁজুন', 'narayanganj-news' ) . '" autocomplete="off" enterkeyhint="search">';
	$h .= '<button class="nn-field__submit" type="submit"><span class="nn-spinner" aria-hidden="true"></span><span class="nn-field__label" data-busy="' . esc_attr__( 'খুঁজছি…', 'narayanganj-news' ) . '">' . esc_html__( 'খুঁজুন', 'narayanganj-news' ) . '</span></button>';
	$h .= '</form>';
	return apply_filters( 'nn_search_form', $h, $id );
}

/**
 * Contact lines from the settings textarea: "ফোন: ০১৭…" becomes a bold label
 * and a value; emails become mailto links.
 *
 * @return string
 */
function nn_contact_html() {
	$raw = trim( (string) nn_opt( 'nn_contact' ) );
	if ( '' === $raw ) {
		return '';
	}
	$h = '';
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$label = '';
		if ( preg_match( '/^([^:：]{1,30})[:：]\s*(.+)$/u', $line, $m ) ) {
			$label = $m[1];
			$line  = $m[2];
		}
		$val = is_email( $line ) ? '<a href="' . esc_url( 'mailto:' . $line ) . '">' . esc_html( $line ) . '</a>' : esc_html( $line );
		$h  .= '<p class="nn-footer__line">' . ( '' !== $label ? '<strong>' . esc_html( $label ) . ':</strong> ' : '' ) . $val . '</p>';
	}
	return $h;
}

/**
 * App download buttons (only the ones with a URL).
 *
 * @return string
 */
function nn_app_buttons() {
	$items = array(
		'android' => array( (string) nn_opt( 'nn_app_android' ), 'গুগল প্লে' ),
		'ios'     => array( (string) nn_opt( 'nn_app_ios' ), 'অ্যাপ স্টোর' ),
	);
	$h = '';
	foreach ( $items as $k => $it ) {
		if ( '' === trim( $it[0] ) ) {
			continue;
		}
		$h .= '<a class="nn-app nn-app--' . esc_attr( $k ) . '" href="' . esc_url( $it[0] ) . '" target="_blank" rel="noopener"><span class="nn-app__small">' . esc_html__( 'ডাউনলোড করুন', 'narayanganj-news' ) . '</span><span class="nn-app__big">' . esc_html( $it[1] ) . '</span></a>';
	}
	return '' === $h ? '' : '<div class="nn-apps">' . $h . '</div>';
}

/**
 * Category terms are only needed when permalinks contain %category%.
 *
 * @return bool
 */
function nn_needs_term_cache() {
	return false !== strpos( (string) get_option( 'permalink_structure' ), '%category%' );
}
