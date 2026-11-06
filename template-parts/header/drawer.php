<?php
/**
 * Off-canvas drawer: search, প্রচ্ছদ, flat category list (sub-items become an
 * accordion), legal links and social links. Closes with X, Esc or the scrim.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

$nn_items = nn_nav_items( 'drawer' );
$nn_home  = array(
	'title'    => __( 'প্রচ্ছদ', 'narayanganj-news' ),
	'url'      => home_url( '/' ),
	'active'   => is_front_page(),
	'children' => array(),
);
?>
<div class="nn-drawer" id="nn-drawer" hidden>
	<div class="nn-drawer__scrim" data-nn-drawer-close></div>
	<div class="nn-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'মেনু', 'narayanganj-news' ); ?>" tabindex="-1">
		<div class="nn-drawer__top">
			<?php echo nn_wordmark( 's', true, 'only' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<button class="nn-iconbtn" type="button" data-nn-drawer-close aria-label="<?php esc_attr_e( 'মেনু বন্ধ করুন', 'narayanganj-news' ); ?>"><?php echo nn_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</div>
		<nav class="nn-drawer__nav" aria-label="<?php esc_attr_e( 'সব বিভাগ', 'narayanganj-news' ); ?>">
			<ul class="nn-drawer__list">
				<li><?php echo nn_nav_link( $nn_home, 'nn-drawer__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
				<?php foreach ( $nn_items as $nn_i => $nn_n ) : ?>
					<?php if ( $nn_n['children'] ) : ?>
						<?php $nn_sub = 'nn-sub-' . (int) $nn_i; ?>
						<li class="has-sub">
							<div class="nn-drawer__row">
								<?php echo nn_nav_link( $nn_n, 'nn-drawer__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<button class="nn-iconbtn nn-drawer__toggle" type="button" data-nn-acc aria-expanded="false" aria-controls="<?php echo esc_attr( $nn_sub ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: section name */ __( '%s: উপবিভাগ', 'narayanganj-news' ), $nn_n['title'] ) ); ?>"><?php echo nn_icon( 'chevron-down', 'nn-chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
							</div>
							<ul class="nn-drawer__sub" id="<?php echo esc_attr( $nn_sub ); ?>" hidden>
								<?php foreach ( $nn_n['children'] as $nn_c ) : ?>
									<li><?php echo nn_nav_link( $nn_c, 'nn-drawer__sublink' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php else : ?>
						<li><?php echo nn_nav_link( $nn_n, 'nn-drawer__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</nav>
	</div>
</div>
