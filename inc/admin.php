<?php
/**
 * Settings screen: a dedicated item in the admin menu (not the Customizer).
 * All values live in ONE small option, `nn_settings`.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field map: key => type, label, section, help, extra.
 *
 * @return array
 */
function nn_fields() {
	return array(
		'nn_brand_name'         => array( 'text', 'general', 'Site name', 'Shown in the header text, footer and copyright. Empty = the WordPress Site Title. A logo uploaded in Appearance → Customize → Site Identity replaces the text in the header.' ),
		'nn_about_text'         => array( 'textarea', 'footer', 'Footer about text', '{site} is replaced by the site name.' ),
		'nn_logo'               => array( 'image', 'general', 'Header logo', 'Shown in the header. Empty = the logo from Site Identity, else the site name as text.' ),
		'nn_footer_logo'        => array( 'image', 'general', 'Footer logo', 'A separate logo for the footer. Empty = the site name as text.' ),
		'nn_show_date_line'     => array( 'checkbox', 'general', 'Show today\'s date under the site name (desktop)', 'The date is part of the cached page; purge the edge cache daily if you turn this on.' ),
		'nn_image_mode'         => array( 'select', 'general', 'Default story display', 'Each module and each article can override this.', array( 'photos' => 'Photos', 'text' => 'Text only' ) ),
		'nn_image_placeholders' => array( 'checkbox', 'general', 'Grey box when a story has no photo', 'Keeps the grid aligned.' ),
		'nn_justify_mobile'     => array( 'checkbox', 'general', 'Justify the article body on mobile', '' ),
		'nn_use_bold'           => array( 'checkbox', 'general', 'Use a licensed Bold font for headlines', 'Needs assets/fonts/SolaimanLipi-Bold.woff2. Without the file nothing changes.' ),

		'nn_mode_default'       => array( 'select', 'modes', 'Light / dark mode', 'Auto follows the visitor\'s local time. Visitors can always switch with the button in the header; their choice lasts until the next automatic change.', array( 'auto' => 'Automatic by time of day', 'light' => 'Always light (until the visitor switches)', 'dark' => 'Always dark (until the visitor switches)' ) ),
		'nn_day_start'          => array( 'number', 'modes', 'Light mode starts at (hour, 0–23)', 'Default 6 = 6:00.', array( 0, 23 ) ),
		'nn_night_start'        => array( 'number', 'modes', 'Dark mode starts at (hour, 0–23)', 'Default 18 = 18:00.', array( 0, 23 ) ),
		'nn_card_font'          => array( 'number', 'general', 'Headline size on story cards (px)', 'One size for every story card headline. Default 22.', array( 16, 34 ) ),
		'nn_hdr_bg'             => array( 'color', 'header', 'Header background (light mode)', 'Pick a colour that matches your logo if it has no transparent background. Empty = the theme default.' ),
		'nn_hdr_text'           => array( 'color', 'header', 'Header text and icons (light mode)', '' ),
		'nn_hdr_bg_dark'        => array( 'color', 'header', 'Header background (dark mode)', '' ),
		'nn_hdr_text_dark'      => array( 'color', 'header', 'Header text and icons (dark mode)', '' ),

		'nn_home_cats'          => array( 'catslots', 'home', 'Home page sections', 'Choose which categories appear on the home page, in this order (top to bottom). Leave a slot on Automatic and the theme fills it with your busiest category. A category block you set by hand in the page editor keeps its own choice.' ),
		'nn_footer_name'        => array( 'text', 'footer', 'Company name (under the footer logo)', 'Shown in bold under the logo. Empty = hidden.' ),
		'nn_contact'            => array( 'textarea', 'footer', 'Contact details', 'One line each, for example "ফোন: ০২…" or "ই-মেইল: info@example.com". Text before the first colon becomes bold. Empty = the column is hidden.' ),
		'nn_follow_title'       => array( 'text', 'footer', 'Follow heading', '' ),
		'nn_app_title'          => array( 'text', 'footer', 'App heading', '' ),
		'nn_app_android'        => array( 'url', 'footer', 'Google Play link', 'Empty = no button.' ),
		'nn_app_ios'            => array( 'url', 'footer', 'App Store link', 'Empty = no button.' ),
		'nn_copyright'          => array( 'text', 'footer', 'Copyright line', '{year} and {site} are replaced. You can add a credit, for example "উন্নয়নে: …".' ),
		'nn_ftr_bg'             => array( 'color', 'footer', 'Footer background', 'Both light and dark mode. Empty = the theme default.' ),
		'nn_ftr_text'           => array( 'color', 'footer', 'Footer text and icons', '' ),
		'nn_lead_cat'           => array( 'category', 'home', 'Top story category', 'The big lead story and the list beside it at the top of the home page. All = the newest stories from every category. A lead block set by hand in the page editor keeps its own choice.' ),
		'nn_top_cat'            => array( 'category', 'home', 'Top news grid category', 'The row of top-news cards under the lead story.' ),
		'nn_ticker_on'          => array( 'checkbox', 'home', 'Show the news ticker on the home page', '' ),
		'nn_ticker_label'       => array( 'text', 'home', 'Ticker label', '' ),
		'nn_ticker_count'       => array( 'number', 'home', 'Headlines in the ticker', '', array( 3, 20 ) ),
		'nn_ticker_speed'       => array( 'number', 'home', 'Ticker speed (pixels per second)', '', array( 20, 200 ) ),
		'nn_ticker_cat'         => array( 'category', 'home', 'Ticker category', 'All = the newest stories from every category.' ),

		'nn_track_views'        => array( 'checkbox', 'articles', 'Count article views (for "সর্বাধিক পঠিত")', '' ),
		'nn_fb_app_id'          => array( 'text', 'articles', 'Facebook App ID (optional)', 'Makes the Messenger share button work on desktop too. Without it the button opens the Messenger app on phones only. Create a free app at developers.facebook.com.' ),
		'nn_card_on'            => array( 'checkbox', 'card', 'Show "ছবি ডাউনলোড" under the article photo', 'Builds a square picture with your logo, the date, the article photo and the headline, for downloading or sharing from a phone.' ),
		'nn_card_style'         => array( 'cardstyle', 'card', 'Picture style', 'Pick a design. The previews use your saved logo and colours; save to refresh them.', nn_card_styles() ),
		'nn_card_head'          => array( 'color', 'card', 'Top band colour', 'Behind the logo and date. Empty = a light tint of the main colour. If your logo has a white background, pick white here.' ),
		'nn_card_head_text'     => array( 'color', 'card', 'Date colour', 'Empty = chosen automatically for contrast.' ),
		'nn_card_color'         => array( 'color', 'card', 'Main colour (middle section)', 'Background behind the photo and headline. Default red.' ),
		'nn_card_text'          => array( 'color', 'card', 'Headline colour', 'Empty = chosen automatically for contrast.' ),
		'nn_card_bar'           => array( 'color', 'card', 'Bottom bar colour', 'Empty = a darker shade of the main colour.' ),
		'nn_card_bar_text'      => array( 'color', 'card', 'Bottom text colour', 'Empty = chosen automatically for contrast.' ),
		'nn_card_footer'        => array( 'text', 'card', 'Share picture bottom line', 'Empty = "বিস্তারিত কমেন্টে". You can write any short line.' ),
		'nn_most_read_days'     => array( 'number', 'articles', 'Most read: look back (days)', '', array( 1, 90 ) ),

		'nn_cache_ttl'          => array( 'number', 'perf', 'Fragment cache (seconds)', 'Home modules, ticker, menus and the article side column are built once and reused. 0 = off. Stale copies are served while exactly one visitor rebuilds, so a busy site never stampedes.', array( 0, 3600 ) ),
		'nn_edge_cache'         => array( 'checkbox', 'perf', 'Send edge-cache headers (Cloudflare and other CDNs)', 'Adds Cache-Control with s-maxage for logged-out visitors on public pages. Never for logged-in users, previews, search or 404.' ),
		'nn_edge_ttl'           => array( 'number', 'perf', 'Edge cache time (seconds)', '', array( 0, 86400 ) ),
		'nn_browser_ttl'        => array( 'number', 'perf', 'Browser cache time (seconds)', '', array( 0, 3600 ) ),
		'nn_admin_stats'        => array( 'checkbox', 'perf', 'Show queries · time · memory in the admin bar', 'Only admins see it, on the live site. Use it to check the targets below.' ),

		'nn_social_facebook'    => array( 'url', 'social', 'Facebook page URL', 'Empty = hidden.' ),
		'nn_social_x'           => array( 'url', 'social', 'X profile URL', '' ),
		'nn_social_whatsapp'    => array( 'url', 'social', 'WhatsApp channel URL', '' ),
		'nn_social_youtube'     => array( 'url', 'social', 'YouTube channel URL', '' ),
		'nn_social_instagram'   => array( 'url', 'social', 'Instagram URL', '' ),
		'nn_social_telegram'    => array( 'url', 'social', 'Telegram URL', '' ),
	);
}

/**
 * Section titles.
 *
 * @return array
 */
function nn_sections() {
	return array(
		'general'  => 'General',
		'modes'    => 'Light and dark mode',
		'header'   => 'Header colours',
		'home'     => 'Home page',
		'articles' => 'Articles',
		'card'     => 'Share picture',
		'footer'   => 'Footer',
		'social'   => 'Footer social links',
		'perf'     => 'Speed and caching',
	);
}

/**
 * Tabs: id => [ label, icon, sections shown ].
 *
 * @return array
 */
function nn_tabs() {
	return array(
		'general'  => array( 'General', 'dashicons-admin-generic', array( 'general' ) ),
		'look'     => array( 'Colours and mode', 'dashicons-art', array( 'modes', 'header' ) ),
		'home'     => array( 'Home page', 'dashicons-admin-home', array( 'home' ) ),
		'articles' => array( 'Articles', 'dashicons-media-document', array( 'articles' ) ),
		'card'     => array( 'Share picture', 'dashicons-format-image', array( 'card' ) ),
		'footer'   => array( 'Footer', 'dashicons-align-center', array( 'footer', 'social' ) ),
		'speed'    => array( 'Speed and cache', 'dashicons-performance', array( 'perf' ) ),
	);
}

/**
 * Admin menu item.
 */
function nn_admin_menu() {
	add_menu_page(
		'Theme Settings',
		'Theme Settings',
		'manage_options',
		'nn-settings',
		'nn_settings_page',
		'dashicons-admin-appearance',
		59
	);
}
add_action( 'admin_menu', 'nn_admin_menu' );

/**
 * Register the single option.
 */
function nn_admin_init() {
	register_setting(
		'nn_settings_group',
		'nn_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'nn_sanitize_settings',
			'default'           => array(),
		)
	);
	nn_maybe_migrate();
}
add_action( 'admin_init', 'nn_admin_init' );

/**
 * One-time copy of the old Customizer values into the new option.
 */
function nn_maybe_migrate() {
	if ( false !== get_option( 'nn_settings' ) ) {
		return;
	}
	$out = array();
	foreach ( array_keys( nn_fields() ) as $key ) {
		$v = get_theme_mod( $key, null );
		if ( null !== $v ) {
			$out[ $key ] = $v;
		}
	}
	add_option( 'nn_settings', nn_sanitize_settings( array_merge( $out, array( '_form' => 0 ) ), true ), '', true );
}
add_action( 'after_switch_theme', 'nn_maybe_migrate' );

/**
 * Sanitize everything that was submitted.
 *
 * @param mixed $in    Raw input.
 * @param bool  $quiet Migration: keep absent checkboxes as they are.
 * @return array
 */
function nn_sanitize_settings( $in, $quiet = false ) {
	$in       = is_array( $in ) ? $in : array();
	$defaults = nn_defaults();
	$out      = array();
	foreach ( nn_fields() as $key => $f ) {
		$type    = $f[0];
		$present = array_key_exists( $key, $in );
		if ( 'checkbox' === $type ) {
			if ( $present ) {
				$out[ $key ] = (bool) $in[ $key ];
			} elseif ( ! empty( $in['_form'] ) ) {
				$out[ $key ] = false;
			}
			continue;
		}
		if ( ! $present ) {
			continue;
		}
		$v = $in[ $key ];
		switch ( $type ) {
			case 'number':
				$min = isset( $f[4][0] ) ? $f[4][0] : 0;
				$max = isset( $f[4][1] ) ? $f[4][1] : 9999;
				$v   = max( $min, min( $max, (int) $v ) );
				break;
			case 'color':
				$v = sanitize_hex_color( (string) $v );
				$v = $v ? $v : '';
				break;
			case 'url':
				$v = esc_url_raw( (string) $v );
				break;
			case 'textarea':
				$v = sanitize_textarea_field( (string) $v );
				break;
			case 'select':
			case 'cardstyle':
				$v = ( isset( $f[4] ) && isset( $f[4][ $v ] ) ) ? (string) $v : (string) $defaults[ $key ];
				break;
			case 'image':
				$v = absint( $v );
				break;
			case 'category':
				$v = absint( $v );
				break;
			case 'catslots':
				$v = array_slice( array_map( 'absint', array_values( (array) $v ) ), 0, 8 );
				break;
			default:
				$v = sanitize_text_field( (string) $v );
		}
		$out[ $key ] = $v;
	}
	return $out;
}

/**
 * Assets for the colour pickers (settings page only).
 *
 * @param string $hook Page hook.
 */
function nn_admin_assets( $hook ) {
	if ( 'toplevel_page_nn-settings' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_media();
	// Live previews of the share-picture styles, drawn with the same code as the site.
	wp_enqueue_script( 'nn-card', NN_URI . '/assets/js/card.js', array(), NN_VERSION, true );
	wp_add_inline_script( 'nn-card', 'window.nnCardPreview=' . wp_json_encode( nn_card_data( null ) ) . ';', 'before' );
	wp_add_inline_script( 'nn-card', '(function(){var b=window.nnCardPreview;if(!b||!window.nnCardBuild){return}var c=document.createElement("canvas");c.width=1200;c.height=675;var g=c.getContext("2d");var r=g.createLinearGradient(0,0,1200,675);r.addColorStop(0,"#5b7fa6");r.addColorStop(1,"#c9a27e");g.fillStyle=r;g.fillRect(0,0,1200,675);g.fillStyle="rgba(255,255,255,.4)";g.beginPath();g.arc(600,270,150,0,7);g.fill();g.fillRect(360,440,480,235);b.img=c.toDataURL("image/jpeg",.8);document.querySelectorAll("[data-nn-prev]").forEach(function(im){var d=Object.assign({},b,{style:im.getAttribute("data-nn-prev")});window.nnCardBuild(d).then(function(x){if(x){im.src=URL.createObjectURL(x)}})});var sel=document.querySelector("[data-nn-style]");if(sel){sel.addEventListener("change",function(){document.querySelectorAll("[data-nn-prev]").forEach(function(im){im.hidden=im.getAttribute("data-nn-prev")!==sel.value})})}})();' );
	wp_add_inline_style( 'wp-color-picker', '@font-face{font-family:SolaimanLipi;src:url(' . esc_url( NN_URI . '/assets/fonts/SolaimanLipi.woff2' ) . ') format("woff2");font-display:swap}.nn-stylebox{margin-top:12px;max-width:320px}.nn-stylebox__img{display:block;width:100%;height:auto;border:1px solid #dcdcde;border-radius:8px;background:#f0f0f1}.nn-stylebox__img[hidden]{display:none}.nn-row select[data-nn-style]{width:100%;max-width:520px}.nn-style{display:flex;flex-direction:column;gap:8px;padding:8px;border:2px solid #dcdcde;border-radius:10px;cursor:pointer;background:#fff}.nn-style:has(input:checked){border-color:#6d28d9;box-shadow:0 0 0 3px rgba(109,40,217,.15)}.nn-style{position:relative}.nn-style input{position:absolute;opacity:0}.nn-style:has(input:focus-visible){outline:2px solid #6d28d9;outline-offset:2px}.nn-style__img{display:block;aspect-ratio:1;background:#f0f0f1;border-radius:6px;overflow:hidden}.nn-style__img img{width:100%;height:100%;object-fit:contain;display:block}.nn-style__name{font-size:12px;line-height:1.4;color:#1d2327}' );
	wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".nn-color").wpColorPicker();$(document).on("click",".nn-img-pick",function(e){e.preventDefault();var w=$(this).closest(".nn-imgf");var f=wp.media({title:"Choose image",multiple:false,library:{type:"image"}});f.on("select",function(){var a=f.state().get("selection").first().toJSON();w.find("input[type=hidden]").val(a.id);w.find("img").attr("src",a.sizes&&a.sizes.medium?a.sizes.medium.url:a.url).show();});f.open();});$(document).on("click",".nn-img-clear",function(e){e.preventDefault();var w=$(this).closest(".nn-imgf");w.find("input[type=hidden]").val("");w.find("img").hide();});});' );
	wp_add_inline_style( 'wp-color-picker', '.nn-set{margin-right:20px}.nn-set h1{display:flex;align-items:center;gap:10px;font-weight:600}.nn-ver{font-size:12px;font-weight:500;background:#e8def8;color:#4b1fa8;border-radius:999px;padding:2px 10px}.nn-wrapgrid{display:grid;grid-template-columns:220px minmax(0,1fr);gap:20px;align-items:start;margin-top:16px}.nn-tabs{position:sticky;top:44px;display:flex;flex-direction:column;gap:4px;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:8px}.nn-tab{display:flex;align-items:center;gap:10px;width:100%;padding:10px 12px;border:0;border-radius:8px;background:none;color:#1d2327;font-size:14px;text-align:left;cursor:pointer}.nn-tab:hover{background:#f6f7f7}.nn-tab.is-on{background:#6d28d9;color:#fff}.nn-tab .dashicons{width:18px;height:18px;font-size:18px}.nn-panel{display:none}.nn-panel.is-on{display:block}.nn-card{background:#fff;border:1px solid #dcdcde;border-radius:12px;margin-bottom:16px;overflow:hidden}.nn-card__head{padding:14px 20px;border-bottom:1px solid #eee;background:#fafafa}.nn-card__head h2{margin:0;font-size:15px}.nn-row{display:grid;grid-template-columns:240px minmax(0,1fr);gap:16px;padding:16px 20px;border-bottom:1px solid #f0f0f1}.nn-row:last-child{border-bottom:0}.nn-row__label{font-weight:600;color:#1d2327;padding-top:4px}.nn-row .description{margin:6px 0 0;max-width:62ch;color:#646970}.nn-row input[type=text],.nn-row input[type=url],.nn-row textarea,.nn-row select{max-width:100%}.nn-row .regular-text{width:100%;max-width:420px}.nn-switch{display:inline-flex;align-items:center;gap:8px}.nn-box{background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:12px 16px;margin:12px 20px}.nn-box p{margin:6px 0}.nn-savebar{position:sticky;bottom:0;z-index:20;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:8px;padding:12px 20px;background:#fff;border:1px solid #dcdcde;border-radius:12px;box-shadow:0 -4px 16px rgba(0,0,0,.06)}.nn-savebar .submit{margin:0;padding:0}.nn-hint{color:#646970}@media(max-width:900px){.nn-wrapgrid{grid-template-columns:1fr}.nn-tabs{position:static;flex-direction:row;flex-wrap:wrap}.nn-tab{width:auto}.nn-row{grid-template-columns:1fr}}' );
	wp_add_inline_script( 'wp-color-picker', '(function(){var t=document.querySelectorAll(".nn-tab"),p=document.querySelectorAll(".nn-panel");function show(id){var ok=false;t.forEach(function(b){var on=b.dataset.tab===id;b.classList.toggle("is-on",on);if(on){ok=true}});if(!ok){id=t[0].dataset.tab;t[0].classList.add("is-on")}p.forEach(function(x){x.classList.toggle("is-on",x.dataset.tab===id)});try{localStorage.setItem("nn-tab",id)}catch(e){}history.replaceState(null,"","#"+id)}t.forEach(function(b){b.addEventListener("click",function(){show(b.dataset.tab)})});var h=location.hash.replace("#","");var s="";try{s=localStorage.getItem("nn-tab")||""}catch(e){}show(h||s||"general")})();' );
}
add_action( 'admin_enqueue_scripts', 'nn_admin_assets' );

/**
 * Render one field.
 *
 * @param string $key Key.
 * @param array  $f   Definition.
 */
function nn_render_field( $key, $f ) {
	$name = 'nn_settings[' . $key . ']';
	$val  = nn_opt( $key );
	$id   = 'nn-f-' . $key;
	switch ( $f[0] ) {
		case 'checkbox':
			printf( '<label class="nn-switch"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s> <span>%4$s</span></label>', esc_attr( $id ), esc_attr( $name ), checked( (bool) $val, true, false ), esc_html( 'On' ) );
			break;
		case 'textarea':
			printf( '<textarea class="large-text" rows="4" id="%1$s" name="%2$s">%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( (string) $val ) );
			break;
		case 'cardstyle':
			printf( '<select id="%1$s" name="%2$s" data-nn-style>', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $f[4] as $k => $label ) {
				printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $k ), selected( (string) $val, (string) $k, false ), esc_html( $label ) );
			}
			echo '</select>';
			echo '<div class="nn-stylebox">';
			foreach ( array_keys( $f[4] ) as $k ) {
				printf( '<img alt="" class="nn-stylebox__img" data-nn-prev="%1$s"%2$s>', esc_attr( $k ), (string) $val === (string) $k ? '' : ' hidden' );
			}
			echo '</div>';
			break;
		case 'select':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $f[4] as $k => $label ) {
				printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $k ), selected( (string) $val, (string) $k, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;
		case 'number':
			printf( '<input type="number" class="small-text" id="%1$s" name="%2$s" value="%3$s" min="%4$d" max="%5$d">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ), (int) $f[4][0], (int) $f[4][1] );
			break;
		case 'color':
			printf( '<input type="text" class="nn-color" id="%1$s" name="%2$s" value="%3$s" data-default-color="">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
			break;
		case 'image':
			$src = $val ? wp_get_attachment_image_url( (int) $val, 'medium' ) : '';
			printf(
				'<span class="nn-imgf"><input type="hidden" id="%1$s" name="%2$s" value="%3$s"><img src="%4$s" alt="" style="max-width:220px;max-height:90px;display:%5$s;margin-bottom:8px"><br><button type="button" class="button nn-img-pick">Choose image</button> <button type="button" class="button-link nn-img-clear">Remove</button></span>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) (int) $val ),
				esc_url( (string) $src ),
				$src ? 'block' : 'none'
			);
			break;
		case 'catslots':
			$chosen = array_values( (array) $val );
			for ( $i = 0; $i < 8; $i++ ) {
				echo '<p style="margin:0 0 6px"><label>' . esc_html( 'Section ' . ( $i + 1 ) ) . ' ';
				wp_dropdown_categories(
					array(
						'name'              => $name . '[]',
						'id'                => $id . '-' . $i,
						'selected'          => isset( $chosen[ $i ] ) ? (int) $chosen[ $i ] : 0,
						'show_option_none'  => 'Automatic (busiest category)',
						'option_none_value' => 0,
						'hide_empty'        => false,
						'number'            => 200,
						'hierarchical'      => true,
					)
				);
				echo '</label></p>';
			}
			break;
		case 'category':
			wp_dropdown_categories(
				array(
					'name'             => $name,
					'id'               => $id,
					'selected'         => (int) $val,
					'show_option_all'  => 'All categories',
					'option_none_value' => 0,
					'hide_empty'       => true,
					'number'           => 200,
					'hierarchical'     => true,
				)
			);
			break;
		default:
			printf( '<input type="%1$s" class="regular-text" id="%2$s" name="%3$s" value="%4$s">', 'url' === $f[0] ? 'url' : 'text', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $val ) );
	}
	if ( ! empty( $f[3] ) ) {
		echo '<p class="description">' . esc_html( $f[3] ) . '</p>';
	}
}

/**
 * Settings page.
 */
function nn_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$fields   = nn_fields();
	$sections = nn_sections();
	$tabs     = nn_tabs();
	?>
	<div class="wrap nn-set">
		<h1>Theme Settings <span class="nn-ver"><?php echo esc_html( 'v' . NN_VERSION ); ?></span></h1>
		<?php if ( isset( $_GET['nn_cleared'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>Theme cache cleared. Pages rebuild on the next visit.</p></div>
		<?php endif; ?>
		<?php settings_errors(); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'nn_settings_group' ); ?>
			<input type="hidden" name="nn_settings[_form]" value="1">
			<div class="nn-wrapgrid">
				<div class="nn-tabs" role="tablist">
					<?php foreach ( $tabs as $tid => $t ) : ?>
						<button type="button" class="nn-tab" role="tab" data-tab="<?php echo esc_attr( $tid ); ?>"><span class="dashicons <?php echo esc_attr( $t[1] ); ?>"></span><?php echo esc_html( $t[0] ); ?></button>
					<?php endforeach; ?>
				</div>
				<div class="nn-panels">
					<?php foreach ( $tabs as $tid => $t ) : ?>
						<div class="nn-panel" role="tabpanel" data-tab="<?php echo esc_attr( $tid ); ?>">
							<?php foreach ( $t[2] as $sid ) : ?>
								<div class="nn-card">
									<div class="nn-card__head"><h2><?php echo esc_html( $sections[ $sid ] ); ?></h2></div>
									<?php foreach ( $fields as $key => $f ) : ?>
										<?php
										if ( $f[1] !== $sid ) {
											continue;
										}
										?>
										<div class="nn-row">
											<div class="nn-row__label"><label for="nn-f-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $f[2] ); ?></label></div>
											<div class="nn-row__ctl"><?php nn_render_field( $key, $f ); ?></div>
										</div>
									<?php endforeach; ?>
									<?php if ( 'perf' === $sid ) : ?>
										<?php nn_status_boxes(); ?>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
					<div class="nn-savebar">
						<span class="nn-hint">Changes apply to every tab. Save when you are done.</span>
						<?php submit_button( 'Save changes', 'primary', 'submit', false ); ?>
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Status and Cloudflare notes (Speed tab).
 */
function nn_status_boxes() {
	$cats  = nn_auto_sections();
	$names = array();
	foreach ( $cats as $cid ) {
		$names[] = get_cat_name( $cid );
	}
	?>
	<div class="nn-box">
		<p><strong>Home sections picked automatically:</strong> <?php echo $names ? esc_html( implode( ' · ', $names ) ) : 'none yet (add categories with posts)'; ?></p>
		<p><strong>Persistent object cache:</strong> <?php echo wp_using_ext_object_cache() ? 'yes (fragments cost no database query)' : 'no (fragments are stored as transients; Redis or Memcached makes pages faster)'; ?></p>
		<p><strong>Targets:</strong> under 25–30 queries, 150 ms and 20 MB per article. Open an article while logged in and read the figures in the admin bar.</p>
		<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nn_clear_cache' ), 'nn_clear_cache' ) ); ?>">Clear theme cache</a></p>
	</div>
	<div class="nn-box">
		<p><strong>Cloudflare setup</strong></p>
		<ol>
			<li>Caching → Cache Rules: <em>Eligible for cache</em>; <em>Edge TTL = Use cache-control header if present</em>.</li>
			<li>Custom cache key: ignore the <code>fbclid</code>, <code>utm_*</code> and <code>gclid</code> query strings.</li>
			<li>Bypass when the cookie contains <code>wordpress_logged_in</code> or <code>wp-postpass</code>, and for <code>/wp-admin</code>, <code>/wp-json</code>, <code>/wp-login.php</code>.</li>
			<li>Turn on Brotli, HTTP/3 and Early Hints. Optional: Polish (WebP).</li>
		</ol>
	</div>
	<?php
}

/**
 * "Clear theme cache" button.
 */
function nn_clear_cache_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	check_admin_referer( 'nn_clear_cache' );
	nn_bump_gen();
	wp_safe_redirect( admin_url( 'admin.php?page=nn-settings&nn_cleared=1' ) );
	exit;
}
add_action( 'admin_post_nn_clear_cache', 'nn_clear_cache_action' );
