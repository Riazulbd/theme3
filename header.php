<?php
/**
 * [M01] Masthead + [M02] nav bar / category scroller + sticky-condensed bar.
 *
 * Desktop: masthead (menu · wordmark · search) and nav bar; after scrolling past
 * them a 64px condensed bar slides in. Tablet/mobile: a 64/56px sticky bar and a
 * horizontally scrolling category row.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

$nn_items = nn_nav_items( 'primary' );
$nn_limit = (int) apply_filters( 'nn_primary_nav_limit', 8 );
$nn_main  = array_slice( $nn_items, 0, $nn_limit );
$nn_more  = array_slice( $nn_items, $nn_limit );
$nn_home  = array(
	'title'    => __( 'প্রচ্ছদ', 'narayanganj-news' ),
	'url'      => home_url( '/' ),
	'active'   => is_front_page(),
	'children' => array(),
);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="nn-skip screen-reader-text" href="#nn-main"><?php esc_html_e( 'মূল বিষয়বস্তুতে যান', 'narayanganj-news' ); ?></a>
<span class="nn-sentinel nn-sentinel--top" aria-hidden="true"></span>
<header class="nn-header nn-masthead" id="top">
		<div class="nn-wrap nn-masthead__inner">
			<div class="nn-masthead__left">
				<button class="nn-iconbtn" type="button" data-nn-drawer-open aria-controls="nn-drawer" aria-expanded="false" aria-label="<?php esc_attr_e( 'মেনু খুলুন', 'narayanganj-news' ); ?>"><?php echo nn_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></button>
			</div>
			<div class="nn-masthead__center">
				<?php if ( is_front_page() ) : ?>
					<h1 class="nn-masthead__title"><?php echo nn_wordmark( 'auto', true, true ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?></h1>
				<?php else : ?>
					<div class="nn-masthead__title"><?php echo nn_wordmark( 'auto', true, true ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?></div>
				<?php endif; ?>
				<?php if ( nn_opt( 'nn_show_date_line' ) ) : ?>
					<p class="nn-masthead__date"><?php echo esc_html( nn_date_line() ); ?></p>
				<?php endif; ?>
			</div>
			<div class="nn-masthead__right">
				<?php echo nn_mode_button(); // phpcs:ignore WordPress.Security.EscapeOutput -- static markup. ?>
				<button class="nn-iconbtn nn-search-toggle" type="button" data-nn-search-open aria-controls="nn-search" aria-expanded="false" aria-label="<?php esc_attr_e( 'খুঁজুন', 'narayanganj-news' ); ?>"><?php echo nn_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<?php get_template_part( 'template-parts/header/search' ); ?>
			</div>
		</div>
</header>

	<?php if ( $nn_items ) : ?>
	<nav class="nn-nav" aria-label="<?php esc_attr_e( 'বিভাগ', 'narayanganj-news' ); ?>">
		<div class="nn-wrap">
			<ul class="nn-nav__list">
				<li class="nn-nav__item nn-nav__item--home"><?php echo nn_nav_link( $nn_home ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
				<?php foreach ( $nn_main as $nn_n ) : ?>
					<li class="nn-nav__item"><?php echo nn_nav_link( $nn_n ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
				<?php endforeach; ?>
				<?php foreach ( $nn_more as $nn_n ) : ?>
					<li class="nn-nav__item nn-nav__item--extra"><?php echo nn_nav_link( $nn_n ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
				<?php endforeach; ?>
				<?php if ( $nn_more ) : ?>
					<li class="nn-nav__item nn-nav__item--more">
						<button class="nn-nav__link" type="button" data-nn-more aria-expanded="false" aria-controls="nn-more"><?php esc_html_e( 'আরও', 'narayanganj-news' ); ?><?php echo nn_icon( 'chevron-down', 'nn-chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
						<ul class="nn-nav__dropdown" id="nn-more" hidden>
							<?php foreach ( $nn_more as $nn_n ) : ?>
								<li><?php echo nn_nav_link( $nn_n, 'nn-nav__droplink' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endif; ?>
			</ul>
		</div>
	</nav>
	<?php endif; ?>
	<span class="nn-sentinel nn-sentinel--header" aria-hidden="true"></span>

<div class="nn-condensed" aria-hidden="true" inert>
	<div class="nn-wrap nn-condensed__inner">
		<div class="nn-condensed__left">
			<button class="nn-iconbtn" type="button" data-nn-drawer-open aria-controls="nn-drawer" aria-expanded="false" aria-label="<?php esc_attr_e( 'মেনু খুলুন', 'narayanganj-news' ); ?>"><?php echo nn_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<?php echo nn_wordmark( 's', true, true ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $nn_main ) : ?>
			<nav aria-label="<?php esc_attr_e( 'বিভাগ', 'narayanganj-news' ); ?>">
				<ul class="nn-condensed__nav">
					<?php foreach ( $nn_main as $nn_n ) : ?>
						<li><?php echo nn_nav_link( $nn_n ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
					<?php endforeach; ?>
					<?php if ( $nn_more ) : ?>
						<li><button class="nn-nav__link" type="button" data-nn-drawer-open aria-controls="nn-drawer" aria-expanded="false"><?php esc_html_e( 'আরও', 'narayanganj-news' ); ?></button></li>
					<?php endif; ?>
				</ul>
			</nav>
			<?php endif; ?>
		</div>
		<span class="nn-condensed__tools">
			<?php echo nn_mode_button(); // phpcs:ignore WordPress.Security.EscapeOutput -- static markup. ?>
			<button class="nn-iconbtn" type="button" data-nn-search-open aria-controls="nn-search" aria-expanded="false" aria-label="<?php esc_attr_e( 'খুঁজুন', 'narayanganj-news' ); ?>"><?php echo nn_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</span>
	</div>
</div>

<main id="nn-main" class="nn-main" tabindex="-1">
