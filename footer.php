<?php
/**
 * [M10] Footer: category links, then brand / contact / follow + apps columns,
 * then legal links and the copyright line. Every part is set in Theme Settings
 * and simply disappears when empty.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

$nn_flat   = nn_nav_items( 'footer' );
$nn_about  = trim( str_replace( '{site}', nn_brand(), (string) nn_opt( 'nn_about_text' ) ) );
$nn_name   = trim( (string) nn_opt( 'nn_footer_name' ) );
$nn_contact = nn_contact_html();
$nn_social = nn_social_html();
$nn_apps   = nn_app_buttons();
$nn_follow = ( '' !== $nn_social || '' !== $nn_apps );
$nn_cols   = 1 + ( '' !== $nn_contact ? 1 : 0 ) + ( $nn_follow ? 1 : 0 );
$nn_copy   = str_replace( array( '{year}', '{site}' ), array( nn_bn( wp_date( 'Y' ) ), nn_brand() ), (string) nn_opt( 'nn_copyright' ) );
?>
</main>

<footer class="nn-footer">
	<div class="nn-wrap">
		<?php if ( $nn_flat ) : ?>
			<nav class="nn-footer__cats" aria-label="<?php esc_attr_e( 'বিভাগসমূহ', 'narayanganj-news' ); ?>">
				<ul>
					<?php foreach ( $nn_flat as $nn_n ) : ?>
						<li><?php echo nn_nav_link( $nn_n, 'nn-footer__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<div class="nn-footer__cols" style="<?php echo esc_attr( '--n:' . $nn_cols ); ?>">
			<div class="nn-footer__brand">
				<?php echo nn_wordmark( 'm', true, 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( '' !== $nn_name ) : ?>
					<p class="nn-footer__name"><?php echo esc_html( $nn_name ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $nn_about ) : ?>
					<p class="nn-footer__about"><?php echo esc_html( $nn_about ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $nn_contact ) : ?>
				<div class="nn-footer__contact"><?php echo $nn_contact; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped when built. ?></div>
			<?php endif; ?>
			<?php if ( $nn_follow ) : ?>
				<div class="nn-footer__follow">
					<?php if ( '' !== $nn_social ) : ?>
						<p class="nn-footer__title"><?php echo esc_html( (string) nn_opt( 'nn_follow_title' ) ); ?></p>
						<?php echo $nn_social; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endif; ?>
					<?php if ( '' !== $nn_apps ) : ?>
						<p class="nn-footer__title"><?php echo esc_html( (string) nn_opt( 'nn_app_title' ) ); ?></p>
						<?php echo $nn_apps; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="nn-footer__bar">
			<?php echo nn_legal_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<p class="nn-footer__copy"><?php echo esc_html( apply_filters( 'nn_copyright', $nn_copy ) ); ?></p>
		</div>
	</div>
</footer>

<?php get_template_part( 'template-parts/header/drawer' ); ?>
<?php if ( is_singular( 'post' ) ) : ?>
	<div class="nn-toast" role="status" aria-live="polite" hidden><?php echo nn_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'লিংক কপি হয়েছে', 'narayanganj-news' ); ?></span></div>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
