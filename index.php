<?php
/**
 * Fallback listing: posts page, section (category) pages, tags, search results.
 * Section and search pages were out of the v6 design scope, so this reuses the
 * module look: a section heading and a 4-up grid of story cards.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_search() ) {
	/* translators: %s: search query */
	$nn_title = sprintf( __( 'খোঁজার ফল: %s', 'narayanganj-news' ), get_search_query() );
} elseif ( is_category() || is_tag() || is_tax() ) {
	$nn_title = single_term_title( '', false );
} elseif ( is_home() && ! is_front_page() ) {
	$nn_title = single_post_title( '', false );
} else {
	$nn_title = __( 'সর্বশেষ', 'narayanganj-news' );
}
$nn_img = nn_images_on();
?>
<div class="nn-wrap nn-listing">
	<section class="nn-module nn-m04 nn-m04--n4<?php echo $nn_img ? ' has-img' : ' no-img'; ?>">
		<?php echo nn_heading( $nn_title, '', 'h1' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( have_posts() ) : ?>
			<?php
			global $wp_query;
			if ( $nn_img ) {
				nn_prime_thumbnails( $wp_query->posts );
			}
			echo nn_m04_markup( $wp_query->posts, 4, $nn_img ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		<?php else : ?>
			<p class="nn-listing__empty"><?php esc_html_e( 'এখানে এখনো কোনো খবর নেই।', 'narayanganj-news' ); ?></p>
			<p><button class="nn-field__submit" type="button" data-nn-search-open aria-controls="nn-search" aria-expanded="false"><?php esc_html_e( 'খুঁজুন', 'narayanganj-news' ); ?></button></p>
		<?php endif; ?>
	</section>
	<?php
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => __( 'আগের পাতা', 'narayanganj-news' ),
			'next_text'          => __( 'পরের পাতা', 'narayanganj-news' ),
			'screen_reader_text' => __( 'পাতা', 'narayanganj-news' ),
			'class'              => 'nn-pagination',
		)
	);
	?>
</div>
<?php
get_footer();
