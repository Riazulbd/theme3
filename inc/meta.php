<?php
/**
 * Per-article settings shown in the editor sidebar ("Article display" panel):
 * - _nn_image_mode: '' (site default), 'on' (photo) or 'off' (text-only version).
 * - _nn_source:     source line, printed as "সূত্র: …".
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register post meta.
 */
function nn_register_meta() {
	$auth = static function ( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	};
	register_post_meta(
		'post',
		'_nn_image_mode',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'auth_callback'     => $auth,
			'sanitize_callback' => static function ( $v ) {
				return in_array( $v, array( '', 'on', 'off' ), true ) ? $v : '';
			},
		)
	);
	register_post_meta(
		'post',
		'_nn_source',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'auth_callback'     => $auth,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
}
add_action( 'init', 'nn_register_meta' );

/**
 * Whether an article shows its photo (text-only version when false).
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function nn_post_images_on( $post_id ) {
	return nn_images_on( (string) get_post_meta( $post_id, '_nn_image_mode', true ) );
}
