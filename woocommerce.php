<?php
/**
 * WooCommerce wrapper: shop pages sit inside the theme grid.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="nn-wrap nn-page nn-shop">
	<?php
	if ( function_exists( 'woocommerce_content' ) ) {
		woocommerce_content();
	}
	?>
</div>
<?php
get_footer();
