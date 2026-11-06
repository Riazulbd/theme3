<?php
/**
 * News ticker: a real, continuously scrolling headline strip.
 * Pure CSS animation (no JS needed), pauses on hover/focus and with the pause
 * button, and becomes a plain scrollable row when motion is reduced.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ticker HTML (cached fragment).
 *
 * @return string
 */
function nn_ticker_html() {
	if ( ! nn_opt( 'nn_ticker_on' ) ) {
		return '';
	}
	$count = max( 3, min( 20, (int) nn_opt( 'nn_ticker_count' ) ) );
	$cat   = (int) nn_opt( 'nn_ticker_cat' );
	$speed = max( 20, min( 200, (int) nn_opt( 'nn_ticker_speed' ) ) );
	$label = trim( (string) nn_opt( 'nn_ticker_label' ) );
	$label = '' === $label ? 'শিরোনাম' : $label;

	return nn_remember(
		'ticker|' . $count . '|' . $cat . '|' . $speed . '|' . $label,
		static function () use ( $count, $cat, $speed, $label ) {
			$args = array(
				'post_type'              => 'post',
				'post_status'            => 'publish',
				'posts_per_page'         => $count,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => nn_needs_term_cache(),
				'update_post_meta_cache' => false,
			);
			if ( $cat ) {
				$args['cat'] = $cat;
			}
			$q = new WP_Query( $args );
			if ( ! $q->posts ) {
				return '';
			}
			$chars = 0;
			$items = '';
			foreach ( $q->posts as $p ) {
				$t      = wp_strip_all_tags( get_the_title( $p ) );
				$chars += function_exists( 'mb_strlen' ) ? mb_strlen( $t ) : strlen( $t );
				$items .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( $t ) . '</a></li>';
			}
			// About 11px per Bengali character; one full set must cross the strip at $speed px/s.
			$dur   = max( 20, (int) round( ( $chars * 11 + count( $q->posts ) * 64 ) / $speed ) );
			$clone = str_replace( '<a ', '<a tabindex="-1" ', $items );
			$h     = '<section class="nn-ticker" aria-label="' . esc_attr( $label ) . '">';
			$h    .= '<div class="nn-wrap nn-ticker__in">';
			$h    .= '<span class="nn-ticker__label">' . esc_html( $label ) . '</span>';
			$h    .= '<div class="nn-ticker__view"><ul class="nn-ticker__track" style="--nn-dur:' . (int) $dur . 's">' . $items . '</ul>';
			$h    .= '<ul class="nn-ticker__track nn-ticker__track--copy" aria-hidden="true" style="--nn-dur:' . (int) $dur . 's">' . $clone . '</ul></div>';
			$h    .= '<button class="nn-ticker__btn" type="button" data-nn-ticker aria-pressed="false" aria-label="' . esc_attr__( 'টিকার থামান', 'narayanganj-news' ) . '" data-pause="' . esc_attr__( 'টিকার থামান', 'narayanganj-news' ) . '" data-play="' . esc_attr__( 'টিকার চালু করুন', 'narayanganj-news' ) . '">' . nn_icon( 'pause', 'nn-ticker__pause' ) . nn_icon( 'play', 'nn-ticker__play' ) . '</button>';
			$h    .= '</div></section>';
			return $h;
		}
	);
}

/**
 * Print the ticker (home page only).
 */
function nn_ticker() {
	echo nn_ticker_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped when built.
}
