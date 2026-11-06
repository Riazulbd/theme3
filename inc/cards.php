<?php
/**
 * Story card atom: XL, L, M, S, headline-only (h) and numbered (n).
 * Cards show headline + optional summary + optional image only:
 * never a category, date, time or author.
 *
 * Responsive modifiers (set by the modules):
 *   sum / sum-d / sum-t / sum-m   show the summary (always / desktop / tablet / mobile)
 *   as-s-t / as-s-m               show an XL/L/M card as an S list row on tablet / mobile
 *   th-d / th-t / th-m            show the S-card thumbnail on desktop / tablet / mobile
 *   to-m-below                    an L card uses the M headline size below desktop
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a story card.
 *
 * @param WP_Post|int $post Post.
 * @param array       $o    Options: size, img, sum, cls, thumb, sizes, eager, num, tag.
 * @return string
 */
function nn_card( $post, $o = array() ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$o    = wp_parse_args(
		$o,
		array(
			'size'  => 'm',
			'img'   => false,
			'sum'   => '',
			'cls'   => '',
			'thumb' => '',
			'sizes' => '',
			'eager' => false,
			'num'   => 0,
			'tag'   => 'h3',
		)
	);
	$o    = apply_filters( 'nn_card_options', $o, $post );
	$size = $o['size'];
	$big  = in_array( $size, array( 'xl', 'l', 'm' ), true );
	$img  = (bool) $o['img'];

	$cls = array( 'nn-card', 'nn-card--' . $size );
	if ( $img ) {
		$cls[] = 'has-img';
	}
	if ( $big && $o['sum'] ) {
		$cls[] = $o['sum'];
	}
	if ( in_array( $size, array( 's', 'n' ), true ) && $img && $o['thumb'] ) {
		$cls[] = $o['thumb'];
	}
	if ( $o['cls'] ) {
		$cls[] = $o['cls'];
	}
	$tag   = in_array( $o['tag'], array( 'h2', 'h3', 'h4' ), true ) ? $o['tag'] : 'h3';
	$title = wp_strip_all_tags( get_the_title( $post ) );
	$url   = get_permalink( $post );

	$h = '<article class="' . esc_attr( implode( ' ', $cls ) ) . '">';
	if ( $big && $img ) {
		$h .= '<div class="nn-card__media">' . nn_image( $post, 'xl' === $size ? '3x2' : '16x9', $o['sizes'], $o['eager'] ) . '</div>';
	}
	if ( $o['num'] ) {
		$h .= '<span class="nn-card__num" aria-hidden="true">' . esc_html( nn_bn( $o['num'] ) ) . '</span>';
	}
	$h .= '<' . $tag . ' class="nn-card__title"><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></' . $tag . '>';
	if ( in_array( $size, array( 's', 'n' ), true ) && $img && $o['thumb'] ) {
		$h .= '<div class="nn-card__thumb">' . nn_image( $post, '16x9', $o['sizes'] ? $o['sizes'] : '(min-width: 600px) 180px, 80px' ) . '</div>';
	}
	if ( $big && $o['sum'] ) {
		$summary = nn_summary( $post );
		if ( '' !== $summary ) {
			$h .= '<p class="nn-card__summary">' . esc_html( $summary ) . '</p>';
		}
	}
	$h .= '</article>';
	return apply_filters( 'nn_card_html', $h, $post, $o );
}

/**
 * List of cards: <ul class="nn-list"> with dividers.
 *
 * @param WP_Post[] $posts Posts.
 * @param array     $o     Card options.
 * @param array     $args  class, vis (per-breakpoint visible counts), offset (global index start), tag ul|ol, num_from.
 * @return string
 */
function nn_card_list( $posts, $o, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class'    => '',
			'vis'      => array(),
			'offset'   => 0,
			'total'    => 0,
			'tag'      => 'ul',
			'num_from' => 0,
		)
	);
	if ( ! $posts ) {
		return '';
	}
	$total = $args['total'] ? (int) $args['total'] : count( $posts );
	$tag   = 'ol' === $args['tag'] ? 'ol' : 'ul';
	$start = ( 'ol' === $tag && $args['num_from'] > 1 ) ? ' start="' . (int) $args['num_from'] . '"' : '';
	$h     = '<' . $tag . ' class="nn-list' . ( $args['class'] ? ' ' . esc_attr( $args['class'] ) : '' ) . '"' . $start . '>';
	foreach ( array_values( $posts ) as $i => $p ) {
		$gi = $args['offset'] + $i;
		$co = $o;
		if ( $args['num_from'] ) {
			$co['num'] = $args['num_from'] + $i;
		}
		$vc = $args['vis'] ? nn_vis( $gi, $total, $args['vis'] ) : '';
		$h .= '<li' . ( $vc ? ' class="' . esc_attr( $vc ) . '"' : '' ) . '>' . nn_card( $p, $co ) . '</li>';
	}
	return $h . '</' . $tag . '>';
}

/**
 * Two lists side by side on tablet, one continuous list on desktop and mobile.
 *
 * @param WP_Post[] $posts Posts.
 * @param int       $first Items in the first column.
 * @param array     $o     Card options.
 * @param array     $vis   Visible counts per breakpoint.
 * @param bool      $numbered Numbered (most read).
 * @return string
 */
function nn_split_list( $posts, $first, $o, $vis = array(), $numbered = false ) {
	$n     = count( $posts );
	$first = max( 1, min( $first, $n ) );
	$a     = array_slice( $posts, 0, $first );
	$b     = array_slice( $posts, $first );
	$h     = '<div class="nn-split">';
	$h    .= nn_card_list(
		$a,
		$o,
		array(
			'vis'      => $vis,
			'total'    => $n,
			'tag'      => $numbered ? 'ol' : 'ul',
			'num_from' => $numbered ? 1 : 0,
		)
	);
	if ( $b ) {
		$h .= nn_card_list(
			$b,
			$o,
			array(
				'vis'      => $vis,
				'offset'   => $first,
				'total'    => $n,
				'tag'      => $numbered ? 'ol' : 'ul',
				'num_from' => $numbered ? $first + 1 : 0,
			)
		);
	}
	return $h . '</div>';
}
