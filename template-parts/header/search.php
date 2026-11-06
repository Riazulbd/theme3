<?php
/**
 * Search: inline in the desktop masthead, a full-screen sheet on tablet/mobile.
 * States: empty (hint), typing (suggestions), submit (busy button + skeleton).
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="nn-search" id="nn-search" hidden>
	<div class="nn-search__top">
		<p class="nn-search__title"><?php esc_html_e( 'খুঁজুন', 'narayanganj-news' ); ?></p>
		<button class="nn-iconbtn" type="button" data-nn-search-close aria-label="<?php esc_attr_e( 'খোঁজা বন্ধ করুন', 'narayanganj-news' ); ?>"><?php echo nn_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</div>
	<div class="nn-search__body">
		<?php echo nn_search_form( 'nn-search-input' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
		<button class="nn-iconbtn nn-search__close-inline" type="button" data-nn-search-close aria-label="<?php esc_attr_e( 'খোঁজা বন্ধ করুন', 'narayanganj-news' ); ?>"><?php echo nn_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		<p class="nn-search__hint"><?php esc_html_e( 'খবর, বিষয় বা জায়গার নাম লিখে খুঁজুন। অন্তত দুটি অক্ষর লিখলেই পরামর্শ দেখা যাবে।', 'narayanganj-news' ); ?></p>
		<div class="nn-search__results" aria-live="polite"></div>
	</div>
</div>
