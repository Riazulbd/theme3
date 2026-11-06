<?php
/**
 * Fragment cache with stale-while-revalidate (no stampede).
 *
 * A fragment (finished HTML or a small array) is stored once with an expiry
 * time and the "generation" it was built in. When it is expired or the
 * generation changed (a post or category changed), exactly one request wins a
 * short lock and rebuilds it; every other request keeps serving the old copy.
 * Only a completely cold cache makes visitors build their own.
 *
 * With a persistent object cache (Redis, Memcached) fragments live in memory
 * and cost no database query. Without one they are transients: one query per
 * fragment, which is still far fewer than building the fragment.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Current cache generation (one small autoloaded option).
 *
 * @return int
 */
function nn_gen() {
	static $gen = null;
	if ( null === $gen ) {
		$gen = (int) get_option( 'nn_gen', 1 );
	}
	return $gen;
}

/**
 * Invalidate every fragment (they are rebuilt one at a time, see above).
 */
function nn_bump_gen() {
	update_option( 'nn_gen', (int) ( microtime( true ) * 1000 ) % 2147483647, true );
}

/**
 * Take a short rebuild lock. True when this request may rebuild.
 *
 * @param string $name Lock name.
 * @return bool
 */
function nn_lock( $name ) {
	if ( wp_using_ext_object_cache() ) {
		return wp_cache_add( $name, 1, 'nn', 30 );
	}
	$key = 'nn_lk_' . substr( md5( $name ), 0, 16 );
	if ( get_transient( $key ) ) {
		return false;
	}
	set_transient( $key, 1, 30 );
	return true;
}

/**
 * Release a rebuild lock.
 *
 * @param string $name Lock name.
 */
function nn_unlock( $name ) {
	if ( wp_using_ext_object_cache() ) {
		wp_cache_delete( $name, 'nn' );
	} else {
		delete_transient( 'nn_lk_' . substr( md5( $name ), 0, 16 ) );
	}
}

/**
 * Get a fragment, rebuilding it when needed.
 *
 * @param string   $key      Fragment name (include anything it depends on).
 * @param callable $build    Builds the value.
 * @param int|null $ttl      Seconds it counts as fresh (null = the setting).
 * @return mixed
 */
function nn_remember( $key, $build, $ttl = null ) {
	if ( nn_is_editor_preview() || is_customize_preview() || is_preview() ) {
		return call_user_func( $build );
	}
	$ttl = null === $ttl ? (int) nn_opt( 'nn_cache_ttl' ) : (int) $ttl;
	if ( $ttl <= 0 ) {
		return call_user_func( $build );
	}
	$tkey  = 'nn_f_' . substr( md5( $key ), 0, 24 );
	$entry = get_transient( $tkey );
	$gen   = nn_gen();
	if ( is_array( $entry ) && array_key_exists( 'd', $entry ) ) {
		if ( $entry['e'] > time() && $entry['g'] === $gen ) {
			return $entry['d'];
		}
		// Stale: one request rebuilds, the rest serve the old copy.
		if ( ! nn_lock( $tkey ) ) {
			return $entry['d'];
		}
	}
	$data = call_user_func( $build );
	set_transient(
		$tkey,
		array(
			'd' => $data,
			'e' => time() + $ttl,
			'g' => $gen,
		),
		min( DAY_IN_SECONDS, max( HOUR_IN_SECONDS, $ttl * 6 ) )
	);
	nn_unlock( $tkey );
	return $data;
}

/**
 * Bump the generation when content that appears in fragments changes.
 *
 * @param string  $new  New status.
 * @param string  $old  Old status.
 * @param WP_Post $post Post.
 */
function nn_on_transition( $new, $old, $post ) {
	if ( 'post' === $post->post_type && ( 'publish' === $new || 'publish' === $old ) ) {
		nn_bump_gen();
	}
}
add_action( 'transition_post_status', 'nn_on_transition', 10, 3 );
add_action( 'deleted_post', 'nn_bump_gen' );
add_action( 'created_category', 'nn_bump_gen' );
add_action( 'edited_category', 'nn_bump_gen' );
add_action( 'delete_category', 'nn_bump_gen' );
add_action( 'wp_update_nav_menu', 'nn_bump_gen' );
add_action( 'update_option_nn_settings', 'nn_bump_gen' );
add_action( 'update_option_theme_mods_' . get_option( 'stylesheet' ), 'nn_bump_gen' );
add_action( 'set_post_thumbnail', 'nn_bump_gen' );

/**
 * First install: save default settings and pick the home sections.
 */
function nn_on_activate() {
	nn_maybe_migrate();
	if ( false === get_option( 'nn_settings' ) ) {
		add_option( 'nn_settings', array(), '', true );
	}
	nn_bump_gen();
}
add_action( 'after_switch_theme', 'nn_on_activate', 20 );
