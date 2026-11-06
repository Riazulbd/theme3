<?php
/**
 * Most-read counter for [M06].
 *
 * Article pages send one beacon per browser session (works behind a full-page
 * cache). The count is a single post-meta integer; the most-read list is cached
 * for 10 minutes so the home page adds no extra query on most requests.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST route: POST /wp-json/nn/v1/view/<id>.
 */
function nn_register_view_route() {
	register_rest_route(
		'nn/v1',
		'/view/(?P<id>\d+)',
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'validate_callback' => static function ( $v ) {
						return is_numeric( $v ) && (int) $v > 0;
					},
				),
			),
			'callback'            => 'nn_count_view',
		)
	);
}
add_action( 'rest_api_init', 'nn_register_view_route' );

/**
 * Increment the view count.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function nn_count_view( $req ) {
	if ( ! nn_opt( 'nn_track_views' ) ) {
		return new WP_REST_Response( null, 204 );
	}
	$id   = (int) $req['id'];
	$post = get_post( $id );
	if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_REST_Response( null, 404 );
	}
	$views = (int) get_post_meta( $id, '_nn_views', true );
	update_post_meta( $id, '_nn_views', $views + 1 );
	return new WP_REST_Response( null, 204 );
}

/**
 * Most-read posts: most views among stories from the last N days,
 * topped up with the newest stories while counts are still low.
 *
 * @param int  $n   How many.
 * @param bool $img Photos beside the headlines (loads thumbnails).
 * @return WP_Post[]
 */
function nn_popular_posts( $n = 5, $img = false ) {
	$n    = max( 1, (int) $n );
	$days = max( 1, (int) nn_opt( 'nn_most_read_days' ) );
	$key  = 'nn_pop_' . $n . '_' . $days;
	$ids  = get_transient( $key );
	if ( ! is_array( $ids ) ) {
		$ids = array();
		if ( nn_opt( 'nn_track_views' ) ) {
			$q   = new WP_Query(
				array(
					'post_type'              => 'post',
					'post_status'            => 'publish',
					'posts_per_page'         => $n,
					'meta_key'               => '_nn_views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- cached 10 minutes.
					'orderby'                => 'meta_value_num',
					'order'                  => 'DESC',
					'date_query'             => array( array( 'after' => $days . ' days ago' ) ),
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'fields'                 => 'ids',
				)
			);
			$ids = array_map( 'intval', $q->posts );
		}
		if ( count( $ids ) < $n ) {
			$q   = new WP_Query(
				array(
					'post_type'              => 'post',
					'post_status'            => 'publish',
					'posts_per_page'         => $n - count( $ids ),
					'post__not_in'           => $ids,
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'fields'                 => 'ids',
				)
			);
			$ids = array_merge( $ids, array_map( 'intval', $q->posts ) );
		}
		set_transient( $key, $ids, (int) apply_filters( 'nn_most_read_cache_ttl', 10 * MINUTE_IN_SECONDS ) );
	}
	if ( ! $ids ) {
		return array();
	}
	$q = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'post__in'               => $ids,
			'orderby'                => 'post__in',
			'posts_per_page'         => count( $ids ),
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => nn_needs_term_cache(),
			'update_post_meta_cache' => (bool) $img,
		)
	);
	if ( $img ) {
		nn_prime_thumbnails( $q->posts );
	}
	return apply_filters( 'nn_popular_posts', $q->posts, $n );
}
