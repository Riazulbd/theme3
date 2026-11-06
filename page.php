<?php
/**
 * Static pages (about, contact, privacy, terms): title + justified body on the article measure.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="nn-wrap nn-page">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'nn-article' ); ?>>
			<header class="nn-article__head">
				<?php the_title( '<h1 class="nn-article__title">', '</h1>' ); ?>
			</header>
			<div class="nn-article__body nn-prose">
				<?php the_content(); ?>
			</div>
		</article>
	</div>
	<?php
endwhile;

get_footer();
