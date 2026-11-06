<?php
/**
 * Not found.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="nn-wrap nn-page nn-404">
	<article class="nn-article">
		<header class="nn-article__head">
			<h1 class="nn-article__title"><?php esc_html_e( 'পাতাটি পাওয়া যায়নি', 'narayanganj-news' ); ?></h1>
			<p class="nn-article__deck"><?php esc_html_e( 'ঠিকানাটি বদলে গেছে বা পাতাটি সরিয়ে নেওয়া হয়েছে। নিচে খুঁজে দেখুন, অথবা প্রচ্ছদে ফিরে যান।', 'narayanganj-news' ); ?></p>
		</header>
		<p><button class="nn-field__submit" type="button" data-nn-search-open aria-controls="nn-search" aria-expanded="false"><?php esc_html_e( 'খুঁজুন', 'narayanganj-news' ); ?></button></p>
		<p><a class="nn-backtotop" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'প্রচ্ছদে ফিরুন', 'narayanganj-news' ); ?></a></p>
	</article>
</div>
<?php
get_footer();
