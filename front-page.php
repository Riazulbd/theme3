<?php
/**
 * Home page. Renders the static front page's blocks (editors add, remove,
 * reorder and fill modules there). With no static page, or an empty one, it
 * renders the approved v6 sample order so a fresh install looks right.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

get_header();
nn_ticker();
?>
<div class="nn-wrap nn-home">
	<?php
	// The finished home modules are one cached fragment (rebuilt by a single visitor when stale).
	$nn_content = '';
	if ( 'page' === get_option( 'show_on_front' ) && have_posts() ) {
		the_post();
		$nn_content = (string) get_post()->post_content;
	}
	$nn_markup = '' !== trim( $nn_content ) ? $nn_content : nn_home_markup( ! nn_images_on() );
	echo nn_remember( // phpcs:ignore WordPress.Security.EscapeOutput -- block output is escaped by the renderers.
		'home|' . md5( $nn_markup . nn_opt( 'nn_image_mode' ) ),
		static function () use ( $nn_markup ) {
			return do_blocks( $nn_markup );
		}
	);
	?>
</div>
<?php
get_footer();
