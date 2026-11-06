<?php
/**
 * Share row: Facebook, X, WhatsApp, copy link (toast "লিংক কপি হয়েছে").
 * Plain links: no third-party scripts.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

$nn_url   = get_permalink();
$nn_u     = rawurlencode( $nn_url );
$nn_t     = rawurlencode( wp_strip_all_tags( get_the_title() ) );
$nn_links = apply_filters(
	'nn_share_links',
	array(
		'facebook' => array( 'https://www.facebook.com/sharer/sharer.php?u=' . $nn_u, __( 'ফেসবুকে শেয়ার করুন', 'narayanganj-news' ) ),
		'whatsapp' => array( 'https://wa.me/?text=' . $nn_t . '%20' . $nn_u, __( 'হোয়াটসঅ্যাপে শেয়ার করুন', 'narayanganj-news' ) ),
		'messenger' => array( nn_messenger_url( $nn_url ), __( 'মেসেঞ্জারে শেয়ার করুন', 'narayanganj-news' ) ),
		'telegram' => array( 'https://t.me/share/url?url=' . $nn_u . '&text=' . $nn_t, __( 'টেলিগ্রামে শেয়ার করুন', 'narayanganj-news' ) ),
	)
);
?>
<div class="nn-share">
	<span class="nn-share__label"><?php esc_html_e( 'শেয়ার করুন', 'narayanganj-news' ); ?></span>
	<?php foreach ( $nn_links as $nn_net => $nn_l ) : ?>
		<a class="nn-iconbtn nn-brand nn-brand--<?php echo esc_attr( $nn_net ); ?>" href="<?php echo esc_url( $nn_l[0], array( 'http', 'https', 'fb-messenger' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $nn_l[1] ); ?>"><?php echo nn_icon( $nn_net ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	<?php endforeach; ?>
	<button class="nn-iconbtn" type="button" data-nn-copy="<?php echo esc_url( $nn_url ); ?>" aria-label="<?php esc_attr_e( 'লিংক কপি করুন', 'narayanganj-news' ); ?>"><?php echo nn_icon( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
</div>
