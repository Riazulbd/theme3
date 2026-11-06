<?php
/**
 * Home page patterns built only from the module blocks.
 * The sample order follows the approved v6 home page:
 * M03-A · M04 4-up · M08 (M07-A + M05) · M07-B · M07-D · M08 (M07-C + M06 1–10) · M07-E.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block markup of the sample home page.
 *
 * @param bool $text_only Text newspaper mode (every module Image = Off).
 * @return string
 */
function nn_home_markup( $text_only = false ) {
	$i = $text_only ? ',"image":"off"' : '';
	$m = '<!-- wp:nn/lead-story {"variant":"a"' . $i . '} /-->' . "\n"
		. '<!-- wp:nn/top-news {"variant":"4"' . $i . '} /-->' . "\n"
		. '<!-- wp:nn/lead-rail -->' . "\n"
		. '<!-- wp:nn/slot {"area":"main"} -->' . "\n"
		. '<!-- wp:nn/category-block {"variant":"a"' . $i . '} /-->' . "\n"
		. '<!-- /wp:nn/slot -->' . "\n"
		. '<!-- wp:nn/slot {"area":"rail"} -->' . "\n"
		. '<!-- wp:nn/latest {"variant":"sidebar","showLink":true} /-->' . "\n"
		. '<!-- /wp:nn/slot -->' . "\n"
		. '<!-- /wp:nn/lead-rail -->' . "\n"
		. '<!-- wp:nn/category-block {"variant":"b"' . $i . '} /-->' . "\n"
		. '<!-- wp:nn/category-block {"variant":"d"' . $i . '} /-->' . "\n"
		. '<!-- wp:nn/lead-rail -->' . "\n"
		. '<!-- wp:nn/slot {"area":"main"} -->' . "\n"
		. '<!-- wp:nn/category-block {"variant":"c"' . $i . '} /-->' . "\n"
		. '<!-- /wp:nn/slot -->' . "\n"
		. '<!-- wp:nn/slot {"area":"rail"} -->' . "\n"
		. '<!-- wp:nn/most-read {"count":10} /-->' . "\n"
		. '<!-- /wp:nn/slot -->' . "\n"
		. '<!-- /wp:nn/lead-rail -->' . "\n"
		. '<!-- wp:nn/category-block {"variant":"e"} /-->';
	return apply_filters( 'nn_home_markup', $m, $text_only );
}

/**
 * Register patterns.
 */
function nn_register_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}
	register_block_pattern_category( 'nn-home', array( 'label' => __( 'সংবাদপট: প্রচ্ছদ (Home pages)', 'narayanganj-news' ) ) );

	register_block_pattern(
		'nn/home-sample',
		array(
			'title'       => __( 'Home: v6 sample (photos)', 'narayanganj-news' ),
			'description' => __( 'The approved v6 home page assembled from the module blocks.', 'narayanganj-news' ),
			'categories'  => array( 'nn-home' ),
			'blockTypes'  => array( 'core/post-content' ),
			'content'     => nn_home_markup( false ),
		)
	);
	register_block_pattern(
		'nn/home-text-only',
		array(
			'title'       => __( 'Home: v6 sample (text-only)', 'narayanganj-news' ),
			'description' => __( 'Same order with every module set to Image = Off (text newspaper mode).', 'narayanganj-news' ),
			'categories'  => array( 'nn-home' ),
			'blockTypes'  => array( 'core/post-content' ),
			'content'     => nn_home_markup( true ),
		)
	);
	register_block_pattern(
		'nn/rail-latest',
		array(
			'title'      => __( 'Lead + rail: category block with latest', 'narayanganj-news' ),
			'categories' => array( 'nn-home' ),
			'content'    => '<!-- wp:nn/lead-rail --><!-- wp:nn/slot {"area":"main"} --><!-- wp:nn/category-block {"variant":"a"} /--><!-- /wp:nn/slot --><!-- wp:nn/slot {"area":"rail"} --><!-- wp:nn/latest {"variant":"sidebar"} /--><!-- /wp:nn/slot --><!-- /wp:nn/lead-rail -->',
		)
	);
	register_block_pattern(
		'nn/rail-most-read',
		array(
			'title'      => __( 'Lead + rail: category block with most read', 'narayanganj-news' ),
			'categories' => array( 'nn-home' ),
			'content'    => '<!-- wp:nn/lead-rail --><!-- wp:nn/slot {"area":"main"} --><!-- wp:nn/category-block {"variant":"c"} /--><!-- /wp:nn/slot --><!-- wp:nn/slot {"area":"rail"} --><!-- wp:nn/most-read {"count":10} /--><!-- /wp:nn/slot --><!-- /wp:nn/lead-rail -->',
		)
	);
}
add_action( 'init', 'nn_register_patterns' );
