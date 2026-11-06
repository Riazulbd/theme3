<?php
/**
 * Article page (design §8): headline, deck, share, photo + caption (or the
 * text-only version), body, source line, share, "আরও পড়ুন" 3-up, then the rail
 * (latest + most read; sticky beside the article on desktop, after related
 * stories on tablet/mobile) and the back-to-top button.
 *
 * Never shown: category label, author/desk byline, date/time, comments, ads, video.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$nn_id  = get_the_ID();
	$nn_img = nn_post_images_on( $nn_id );
	$nn_src = trim( (string) get_post_meta( $nn_id, '_nn_source', true ) );
	nn_shown( array( $nn_id ) );
	?>
	<div class="nn-wrap nn-single<?php echo $nn_img ? '' : ' is-text-only'; ?>">
		<div class="nn-single__grid">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'nn-article' ); ?>>
				<header class="nn-article__head">
					<?php the_title( '<h1 class="nn-article__title">', '</h1>' ); ?>
					<?php if ( has_excerpt() ) : ?>
						<p class="nn-article__deck"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
					<?php endif; ?>
				</header>

				<?php get_template_part( 'template-parts/article/share' ); ?>

				<?php if ( $nn_img && has_post_thumbnail() ) : ?>
					<figure class="nn-article__figure">
						<div class="nn-article__media">
							<?php
							the_post_thumbnail(
								'nn-16x9-l',
								array(
									'loading'       => 'eager',
									'fetchpriority' => 'high',
									'decoding'      => 'async',
									'sizes'         => nn_sizes( 792 ),
								)
							);
							?>
						</div>
						<?php $nn_cap = get_the_post_thumbnail_caption(); ?>
						<?php if ( $nn_cap ) : ?>
							<figcaption class="nn-article__caption"><?php echo esc_html( $nn_cap ); ?></figcaption>
						<?php endif; ?>
					</figure>
					<?php echo nn_card_bar( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped when built. ?>
				<?php else : ?>
					<?php echo nn_card_bar( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php endif; ?>

				<div class="nn-article__body nn-prose">
					<?php the_content(); ?>
					<?php
					wp_link_pages(
						array(
							'before' => '<nav class="nn-pages">',
							'after'  => '</nav>',
						)
					);
					?>
				</div>

				<?php if ( '' !== $nn_src ) : ?>
					<p class="nn-article__source"><?php echo esc_html( 'সূত্র: ' . $nn_src ); ?></p>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/article/share' ); ?>
			</article>

			<?php
			// "আরও পড়ুন" (per article) and the rail (shared) are cached fragments.
			$nn_cats = wp_get_post_categories( $nn_id, array( 'number' => 1 ) );
			$nn_cat  = $nn_cats ? (int) $nn_cats[0] : 0;
			echo nn_remember( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped when built.
				'related2|' . $nn_id . '|' . (int) $nn_img,
				static function () use ( $nn_id, $nn_img, $nn_cat ) {
					$related = nn_module_posts( array( 'dedupe' => true ), 3, $nn_img, $nn_cat );
					if ( count( $related ) < 3 ) {
						$related = array_merge( $related, nn_module_posts( array( 'dedupe' => true ), 3 - count( $related ), $nn_img ) );
					}
					$related = apply_filters( 'nn_related_posts', $related, $nn_id );
					if ( ! $related ) {
						return '';
					}
					return '<section class="nn-single__related nn-m04 nn-m04--n3' . ( $nn_img ? ' has-img' : ' no-img' ) . '" aria-label="' . esc_attr__( 'আরও পড়ুন', 'narayanganj-news' ) . '">'
						. nn_heading( __( 'আরও পড়ুন', 'narayanganj-news' ) )
						. nn_m04_markup( $related, 3, $nn_img, false )
						. '</section>';
				},
				600
			);
			?>

			<aside class="nn-single__rail" aria-label="<?php esc_attr_e( 'আরও খবর', 'narayanganj-news' ); ?>">
				<?php
				echo nn_remember( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped when built.
					'rail-v3',
					static function () {
						return nn_render_latest( apply_filters( 'nn_article_latest_args', array( 'variant' => 'sidebar', 'dedupe' => false, 'thumbs' => true ) ) )
							. nn_render_most_read( apply_filters( 'nn_article_most_read_args', array( 'count' => 5, 'thumbs' => true ) ) );
					}
				);
				?>
			</aside>

			<div class="nn-single__top">
				<a class="nn-backtotop" href="#top"><?php echo nn_icon( 'arrow-up' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'উপরে ফিরুন', 'narayanganj-news' ); ?></span></a>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
