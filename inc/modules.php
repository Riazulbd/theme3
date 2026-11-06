<?php
/**
 * Home page module renderers (design IDs in brackets):
 *   [M03] lead story      variants a (image lead + list), b (full-width image), c (text-only)
 *   [M04] top news grid   4-up, 3-up, 2-up
 *   [M05] latest          sidebar (headline list) or row
 *   [M06] most read       numbered 1–5 or 1–10
 *   [M07] category block  a (1 large + 4 list), b (4-up), c (1 large + 2 medium + 3 list),
 *                         d (2 categories side by side), e (3 categories as headline columns)
 *   [M08] lead + rail     8-col main slot + 4-col rail (stacks below desktop)
 *   [M09] section heading title + optional "আরও দেখুন"
 * M01 header, M02 nav and M10 footer are theme templates.
 *
 * Queries are lean: no_found_rows, no term cache, thumbnails primed in one query.
 * Stories already shown higher on the page are skipped ("dedupe", on by default).
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * IDs already shown on this page.
 *
 * @param array $add IDs to add.
 * @return int[]
 */
function nn_shown( $add = array() ) {
	static $ids = array();
	if ( $add ) {
		$ids = array_values( array_unique( array_merge( $ids, array_map( 'intval', (array) $add ) ) ) );
	}
	return $ids;
}

/**
 * Prime featured-image attachment caches for a set of posts (one query).
 *
 * @param WP_Post[] $posts Posts.
 */
function nn_prime_thumbnails( $posts ) {
	$ids = array();
	foreach ( $posts as $p ) {
		$tid = (int) get_post_thumbnail_id( $p );
		if ( $tid ) {
			$ids[] = $tid;
		}
	}
	if ( $ids && function_exists( '_prime_post_caches' ) ) {
		_prime_post_caches( $ids, false, true );
	}
}

/**
 * Fetch posts for a module.
 *
 * Manual picks come first (in the editor's order); the rest is filled with
 * the newest stories (optionally from one category) that are not on the page yet.
 *
 * @param array $a     Block attributes.
 * @param int   $count Posts needed.
 * @param bool  $img   Prime thumbnails.
 * @param int   $cat   Category ID (0 = all).
 * @return WP_Post[]
 */
function nn_module_posts( $a, $count, $img = false, $cat = 0 ) {
	$count  = max( 1, (int) $count );
	$dedupe = ! isset( $a['dedupe'] ) || $a['dedupe'];
	$base   = array(
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'no_found_rows'          => true,
		'ignore_sticky_posts'    => true,
		'update_post_term_cache' => nn_needs_term_cache(),
		'update_post_meta_cache' => (bool) $img, // The featured-image id is post meta: one query for all cards, none for text-only.
	);
	$posts  = array();
	$manual = array();
	if ( isset( $a['source'] ) && 'manual' === $a['source'] && ! empty( $a['posts'] ) ) {
		$manual = array_slice( array_values( array_filter( array_map( 'absint', (array) $a['posts'] ) ) ), 0, $count );
	}
	if ( $manual ) {
		$q     = new WP_Query(
			$base + array(
				'post__in'       => $manual,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $manual ),
			)
		);
		$posts = $q->posts;
	}
	$need = $count - count( $posts );
	if ( $need > 0 ) {
		$args = $base + array( 'posts_per_page' => $need );
		if ( $cat ) {
			$args['cat'] = (int) $cat;
		}
		if ( ! $manual && ! empty( $a['offset'] ) ) {
			$args['offset'] = absint( $a['offset'] );
		}
		$exclude = wp_list_pluck( $posts, 'ID' );
		if ( $dedupe ) {
			$exclude = array_merge( $exclude, nn_shown() );
		}
		if ( $exclude ) {
			$args['post__not_in'] = array_values( array_unique( array_map( 'intval', $exclude ) ) );
		}
		$args  = apply_filters( 'nn_module_query_args', $args, $a, $count );
		$q     = new WP_Query( $args );
		$posts = array_merge( $posts, $q->posts );
	}
	if ( $img && $posts ) {
		nn_prime_thumbnails( $posts );
	}
	nn_shown( wp_list_pluck( $posts, 'ID' ) );
	return apply_filters( 'nn_module_posts', $posts, $a, $count );
}

/**
 * Sections picked automatically: the busiest top-level categories, topped up
 * with busy sub-categories. No hand mapping needed on a new install; the list
 * follows the site as categories fill up. Cached, bounded to 24 terms.
 *
 * @return int[]
 */
function nn_auto_sections() {
	return nn_remember(
		'auto-sections',
		static function () {
			$ids   = array();
			$terms = get_terms(
				array(
					'taxonomy'   => 'category',
					'hide_empty' => true,
					'orderby'    => 'count',
					'order'      => 'DESC',
					'number'     => 40,
					'fields'     => 'all',
				)
			);
			if ( is_wp_error( $terms ) ) {
				return array();
			}
			$default = (int) get_option( 'default_category' );
			foreach ( array( true, false ) as $top_only ) {
				foreach ( $terms as $t ) {
					if ( count( $ids ) >= 24 ) {
						break 2;
					}
					if ( (int) $t->term_id === $default && 'uncategorized' === $t->slug ) {
						continue;
					}
					if ( $top_only && $t->parent ) {
						continue;
					}
					if ( ! in_array( (int) $t->term_id, $ids, true ) ) {
						$ids[] = (int) $t->term_id;
					}
				}
			}
			return $ids;
		},
		HOUR_IN_SECONDS
	);
}

/**
 * Pick categories for category blocks: the chosen ones, then the automatic
 * sections not yet used on this page.
 *
 * @param array $wanted Chosen IDs.
 * @param int   $n      How many.
 * @return int[]
 */
function nn_pick_categories( $wanted, $n ) {
	static $used = array();
	$out = array();
	foreach ( (array) $wanted as $id ) {
		$id = absint( $id );
		if ( $id && ! in_array( $id, $out, true ) ) {
			$term = get_term( $id, 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				$out[] = $id;
			}
		}
	}
	if ( count( $out ) < $n ) {
		// Sections chosen in Theme Settings come first, in slot order; automatic ones fill the rest.
		$chosen = array();
		foreach ( (array) nn_opt( 'nn_home_cats' ) as $cid ) {
			$cid = absint( $cid );
			if ( $cid && ! in_array( $cid, $chosen, true ) && term_exists( $cid, 'category' ) ) {
				$chosen[] = $cid;
			}
		}
		$auto = array_merge( $chosen, nn_auto_sections() );
		foreach ( array( true, false ) as $skip_used ) {
			foreach ( $auto as $id ) {
				if ( count( $out ) >= $n ) {
					break 2;
				}
				if ( in_array( $id, $out, true ) || ( $skip_used && in_array( $id, $used, true ) ) ) {
					continue;
				}
				$out[] = $id;
			}
		}
	}
	$out  = array_slice( $out, 0, $n );
	$used = array_merge( $used, $out );
	return $out;
}

/**
 * [M09] Section heading: 2px rule with the brand tick, title, optional "আরও দেখুন".
 *
 * @param string $title Title.
 * @param string $url   Link (empty = title only).
 * @param string $tag   Heading tag.
 * @return string
 */
function nn_heading( $title, $url = '', $tag = 'h2' ) {
	$tag = in_array( $tag, array( 'h1', 'h2', 'h3' ), true ) ? $tag : 'h2';
	$h   = '<div class="nn-heading"><span class="nn-heading__rule" aria-hidden="true"></span><div class="nn-heading__row">';
	$h  .= '<' . $tag . ' class="nn-heading__title">' . esc_html( $title ) . '</' . $tag . '>';
	if ( $url ) {
		$h .= '<a class="nn-heading__more" href="' . esc_url( $url ) . '">' . esc_html__( 'আরও দেখুন', 'narayanganj-news' ) . '<span class="screen-reader-text">: ' . esc_html( $title ) . '</span>' . nn_icon( 'chevron-right' ) . '</a>';
	}
	return apply_filters( 'nn_heading_html', $h . '</div></div>', $title, $url );
}

/**
 * Opening tag of a module.
 *
 * @param string $cls   Classes.
 * @param array  $a     Attributes.
 * @param string $label Accessible label.
 * @return string
 */
function nn_module_open( $cls, $a, $label = '' ) {
	$classes = 'nn-module ' . $cls;
	if ( ! empty( $a['className'] ) ) {
		$classes .= ' ' . $a['className'];
	}
	$id = ! empty( $a['anchor'] ) ? ' id="' . esc_attr( $a['anchor'] ) . '"' : '';
	return '<section class="' . esc_attr( $classes ) . '"' . $id . ( $label ? ' aria-label="' . esc_attr( $label ) . '"' : '' ) . '>';
}

/**
 * Nothing to show: no markup on the site (no empty gap), a note in the editor.
 *
 * @param string $name Module name.
 * @return string
 */
function nn_module_empty( $name ) {
	if ( nn_is_editor_preview() ) {
		return '<div class="nn-empty">' . esc_html( $name ) . ': ' . esc_html__( 'এখানে দেখানোর মতো কোনো খবর নেই। মডিউলটি সরিয়ে দিন বা অন্য উৎস বেছে নিন।', 'narayanganj-news' ) . '</div>';
	}
	return '';
}

/**
 * Slot the block sits in (M08 main / rail), from block context.
 *
 * @param WP_Block|null $block Block.
 * @return string
 */
function nn_block_area( $block ) {
	if ( $block instanceof WP_Block && isset( $block->context['nn/area'] ) ) {
		return (string) $block->context['nn/area'];
	}
	return '';
}

/**
 * Category of a source=category module.
 *
 * @param array  $a       Attributes.
 * @param string $setting Theme Settings key used when the block has no category of its own.
 * @return int
 */
function nn_source_cat( $a, $setting = '' ) {
	if ( isset( $a['source'] ) && 'category' === $a['source'] ) {
		return absint( $a['category'] );
	}
	// Not set by hand on the block: use the category chosen in Theme Settings, if any.
	if ( $setting && ( ! isset( $a['source'] ) || 'latest' === $a['source'] ) ) {
		return absint( nn_opt( $setting ) );
	}
	return 0;
}

/**
 * "আরও দেখুন" target for the latest list: the posts page, when one is set.
 *
 * @return string
 */
function nn_latest_url() {
	$page = (int) get_option( 'page_for_posts' );
	return apply_filters( 'nn_latest_url', $page ? get_permalink( $page ) : '' );
}

/* ---------------------------------------------------------------- M03 */

/**
 * [M03] Lead story.
 *
 * @param array         $a       Attributes.
 * @param string        $content Inner content.
 * @param WP_Block|null $block   Block.
 * @return string
 */
function nn_render_lead_story( $a, $content = '', $block = null ) {
	$a    = wp_parse_args(
		(array) $a,
		array(
			'variant'  => 'a',
			'image'    => 'default',
			'source'   => 'latest',
			'category' => 0,
			'posts'    => array(),
			'offset'   => 0,
			'dedupe'   => true,
		)
	);
	$v    = in_array( $a['variant'], array( 'a', 'b', 'c' ), true ) ? $a['variant'] : 'a';
	$img  = ( 'c' === $v ) ? false : nn_images_on( $a['image'] );
	$need = array(
		'a' => 6,
		'b' => 3,
		'c' => 4,
	);
	$p    = nn_module_posts( $a, $need[ $v ], $img, nn_source_cat( $a, 'nn_lead_cat' ) );
	if ( ! $p ) {
		return nn_module_empty( 'M03' );
	}
	$h = nn_module_open( 'nn-m03 nn-m03--' . $v . ( $img ? ' has-img' : ' no-img' ), $a, __( 'প্রধান খবর', 'narayanganj-news' ) );

	if ( 'a' === $v ) {
		$h .= '<div class="nn-m03__main">' . nn_card(
			$p[0],
			array(
				'size'  => 'xl',
				'img'   => $img,
				'sum'   => 'sum',
				'eager' => true,
				'tag'   => 'h2',
				'sizes' => nn_sizes( 792 ),
			)
		) . '</div>';
		if ( isset( $p[1] ) ) {
			$h .= '<div class="nn-m03__side">';
			$h .= '<div class="nn-m03__second">' . nn_card(
				$p[1],
				array(
					'size'  => 'm',
					'img'   => $img,
					'sum'   => $img ? 'sum-t' : 'sum-d sum-t',
					'cls'   => 'as-s-m',
					'sizes' => nn_sizes( 384, 'calc(50vw - 44px)', '80px' ),
				)
			) . '</div>';
			$h .= nn_card_list(
				array_slice( $p, 2, 4 ),
				array(
					'size'  => 's',
					'img'   => $img,
					'thumb' => 'th-m',
					'sizes' => '80px',
				),
				array(
					'class' => 'nn-m03__list',
					'vis'   => array(
						'd' => 4,
						't' => 3,
						'm' => 3,
					),
				)
			);
			$h .= '</div>';
		}
	} elseif ( 'b' === $v ) {
		if ( $img ) {
			$h .= '<a class="nn-m03__hero" href="' . esc_url( get_permalink( $p[0] ) ) . '" tabindex="-1" aria-hidden="true">' . nn_image( $p[0], '16x9', '(min-width: 1248px) 1200px, (min-width: 600px) calc(100vw - 64px), calc(100vw - 40px)', true ) . '</a>';
		}
		$h .= '<div class="nn-m03__main">' . nn_card(
			$p[0],
			array(
				'size' => 'xl',
				'sum'  => 'sum',
				'tag'  => 'h2',
			)
		) . '</div>';
		$h .= '<div class="nn-m03__side">' . nn_card_list( array_slice( $p, 1, 2 ), array( 'size' => 'h' ), array( 'class' => 'nn-m03__list' ) ) . '</div>';
	} else {
		$h .= '<div class="nn-m03__main">' . nn_card(
			$p[0],
			array(
				'size' => 'xl',
				'sum'  => 'sum',
				'tag'  => 'h2',
			)
		) . '</div>';
		$h .= '<div class="nn-m03__rule" aria-hidden="true"></div>';
		$h .= nn_card_list(
			array_slice( $p, 1, 3 ),
			array(
				'size' => 'm',
				'sum'  => 'sum-d',
				'cls'  => 'as-s-t as-s-m',
			),
			array( 'class' => 'nn-m03__list' )
		);
	}
	return $h . '</section>';
}

/* ---------------------------------------------------------------- M04 */

/**
 * [M04] grid markup, shared by the block and the article "আরও পড়ুন" block.
 *
 * @param WP_Post[] $posts Posts.
 * @param int       $n     4, 3 or 2.
 * @param bool      $img   Photos.
 * @param bool      $summaries Show the summary under each headline.
 * @return string
 */
function nn_m04_markup( $posts, $n, $img, $summaries = true ) {
	$span = array(
		4 => 3,
		3 => 4,
		2 => 6,
	);
	$dw   = array(
		4 => 282,
		3 => 384,
		2 => 588,
	);
	$h    = '<ul class="nn-m04__grid nn-g">';
	foreach ( array_values( $posts ) as $i => $p ) {
		$sum = array();
		if ( ! $img || $n < 4 ) {
			$sum[] = 'sum-d';
		}
		if ( 3 !== $n && ( ! $img || 2 === $n ) ) {
			$sum[] = 'sum-t';
		}
		if ( 0 === $i ) {
			$sum[] = 'sum-m';
		}
		if ( ! $summaries ) {
			$sum = array();
		}
		$cls = array();
		if ( 2 === $n ) {
			$cls[] = 'to-m-below';
		}
		if ( 3 === $n ) {
			$cls[] = 'as-s-t';
		}
		if ( $i > 0 ) {
			$cls[] = 'as-s-m';
		}
		$h .= '<li class="d' . $span[ $n ] . ( 3 !== $n ? ' t4' : '' ) . '">' . nn_card(
			$p,
			array(
				'size'  => ( 2 === $n ) ? 'l' : 'm',
				'img'   => $img,
				'sum'   => implode( ' ', $sum ),
				'cls'   => implode( ' ', $cls ),
				'sizes' => nn_sizes( $dw[ $n ], ( 3 === $n ) ? '180px' : 'calc(50vw - 44px)', ( 0 === $i ) ? 'calc(100vw - 40px)' : '80px' ),
			)
		) . '</li>';
	}
	return $h . '</ul>';
}

/**
 * [M04] Top news grid.
 *
 * @param array         $a       Attributes.
 * @param string        $content Inner content.
 * @param WP_Block|null $block   Block.
 * @return string
 */
function nn_render_top_news( $a, $content = '', $block = null ) {
	$a   = wp_parse_args(
		(array) $a,
		array(
			'variant'  => '4',
			'image'    => 'default',
			'source'   => 'latest',
			'category' => 0,
			'posts'    => array(),
			'offset'   => 0,
			'dedupe'   => true,
			'title'    => '',
		)
	);
	$n   = in_array( (int) $a['variant'], array( 2, 3, 4 ), true ) ? (int) $a['variant'] : 4;
	$img = nn_images_on( $a['image'] );
	$p   = nn_module_posts( $a, $n, $img, nn_source_cat( $a, 'nn_top_cat' ) );
	if ( ! $p ) {
		return nn_module_empty( 'M04' );
	}
	$title = trim( (string) $a['title'] );
	$h     = nn_module_open( 'nn-m04 nn-m04--n' . $n . ( $img ? ' has-img' : ' no-img' ), $a, $title ? $title : __( 'শীর্ষ খবর', 'narayanganj-news' ) );
	if ( $title ) {
		$h .= nn_heading( $title );
	}
	return $h . nn_m04_markup( $p, $n, $img ) . '</section>';
}

/* ---------------------------------------------------------------- M05 */

/**
 * [M05] Latest "দিনের সর্বশেষ": headlines only, no timestamps.
 *
 * @param array         $a       Attributes.
 * @param string        $content Inner content.
 * @param WP_Block|null $block   Block.
 * @return string
 */
function nn_render_latest( $a, $content = '', $block = null ) {
	$a     = wp_parse_args(
		(array) $a,
		array(
			'variant'  => 'sidebar',
			'title'    => 'দিনের সর্বশেষ',
			'showLink' => true,
			'linkUrl'  => '',
			'count'    => 8,
			'source'   => 'latest',
			'category' => 0,
			'posts'    => array(),
			'offset'   => 0,
			'dedupe'   => true,
			'thumbs'   => false,
		)
	);
	$row   = ( 'row' === $a['variant'] );
	$n     = $row ? 4 : max( 2, min( 12, (int) $a['count'] ) );
	$th    = ! empty( $a['thumbs'] );
	$p     = nn_module_posts( $a, $n, $th, nn_source_cat( $a ) );
	$title = trim( (string) $a['title'] ) ? $a['title'] : 'দিনের সর্বশেষ';
	if ( ! $p ) {
		return nn_module_empty( 'M05' );
	}
	$link = '';
	if ( $a['showLink'] ) {
		$link = $a['linkUrl'] ? $a['linkUrl'] : nn_latest_url();
	}
	$h  = nn_module_open( 'nn-m05 nn-m05--' . ( $row ? 'row' : 'sidebar' ), $a, $title );
	$h .= nn_heading( $title, $link );
	$card  = $th ? array( 'size' => 's', 'img' => true, 'thumb' => 'th-d th-t th-m', 'sizes' => '96px' ) : array( 'size' => 'h' );
	$h    .= nn_split_list( $p, (int) ceil( count( $p ) / 2 ), $card, $row ? array() : array( 'm' => min( count( $p ), 6 ) ) );
	return $h . '</section>';
}

/* ---------------------------------------------------------------- M06 */

/**
 * [M06] Most read "সর্বাধিক পঠিত": numbered with Bengali digits.
 *
 * @param array         $a       Attributes.
 * @param string        $content Inner content.
 * @param WP_Block|null $block   Block.
 * @return string
 */
function nn_render_most_read( $a, $content = '', $block = null ) {
	$a     = wp_parse_args(
		(array) $a,
		array(
			'count'    => 5,
			'title'    => 'সর্বাধিক পঠিত',
			'source'   => 'popular',
			'category' => 0,
			'posts'    => array(),
			'offset'   => 0,
			'dedupe'   => false,
			'thumbs'   => false,
		)
	);
	$n     = ( 10 === (int) $a['count'] ) ? 10 : 5;
	$th    = ! empty( $a['thumbs'] );
	$title = trim( (string) $a['title'] ) ? $a['title'] : 'সর্বাধিক পঠিত';
	if ( 'popular' === $a['source'] ) {
		$p = nn_popular_posts( $n, $th );
	} else {
		$p = nn_module_posts( $a, $n, $th, nn_source_cat( $a ) );
	}
	if ( ! $p ) {
		return nn_module_empty( 'M06' );
	}
	$h  = nn_module_open( 'nn-m06' . ( $th ? ' has-thumbs' : '' ), $a, $title );
	$h .= nn_heading( $title );
	$h .= nn_split_list( $p, ( 10 === $n ) ? 5 : 3, array( 'size' => 'n', 'img' => $th, 'thumb' => $th ? 'th-n' : '', 'sizes' => '96px' ), array(), true );
	return $h . '</section>';
}

/* ---------------------------------------------------------------- M07 */

/**
 * [M07] Category block.
 *
 * @param array         $a       Attributes.
 * @param string        $content Inner content.
 * @param WP_Block|null $block   Block.
 * @return string
 */
function nn_render_category_block( $a, $content = '', $block = null ) {
	$a     = wp_parse_args(
		(array) $a,
		array(
			'variant'    => 'a',
			'image'      => 'default',
			'category'   => 0,
			'categories' => array(),
			'title'      => '',
			'showLink'   => true,
			'dedupe'     => true,
		)
	);
	$v     = in_array( $a['variant'], array( 'a', 'b', 'c', 'd', 'e' ), true ) ? $a['variant'] : 'a';
	$eight = ( 'main' === nn_block_area( $block ) );
	$img   = nn_images_on( $a['image'] );
	$base  = 'nn-m07 nn-m07--' . $v . ( $img ? ' has-img' : ' no-img' ) . ( $eight ? ' is-8' : '' );

	if ( in_array( $v, array( 'a', 'b', 'c' ), true ) ) {
		$cats = nn_pick_categories( array( $a['category'] ), 1 );
		$cid  = $cats ? $cats[0] : 0;
		$need = array(
			'a' => 5,
			'b' => 4,
			'c' => 6,
		);
		$p    = nn_module_posts( $a, $need[ $v ], $img, $cid );
		if ( ! $p ) {
			return nn_module_empty( 'M07' );
		}
		$title = trim( (string) $a['title'] );
		if ( '' === $title ) {
			$title = $cid ? get_cat_name( $cid ) : __( 'সর্বশেষ', 'narayanganj-news' );
		}
		$url = ( $cid && $a['showLink'] ) ? get_category_link( $cid ) : '';
		$h   = nn_module_open( $base, $a, $title ) . nn_heading( $title, $url );

		if ( 'a' === $v ) {
			$col = $eight ? 'd4' : 'd6';
			$h  .= '<div class="nn-m07a nn-g"><div class="' . $col . '">' . nn_card(
				$p[0],
				array(
					'size'  => 'l',
					'img'   => $img,
					'sum'   => 'sum',
					'sizes' => nn_sizes( $eight ? 384 : 588 ),
				)
			) . '</div>';
			if ( count( $p ) > 1 ) {
				$h .= '<div class="' . $col . '">' . nn_card_list(
					array_slice( $p, 1, 4 ),
					array(
						'size'  => 's',
						'img'   => $img,
						'thumb' => $eight ? 'th-t th-m' : 'th-d th-t th-m',
						'sizes' => '(min-width: 600px) 180px, 80px',
					),
					array( 'class' => 'nn-m07a__list' )
				) . '</div>';
			}
			$h .= '</div>';
		} elseif ( 'b' === $v ) {
			$h .= '<ul class="nn-m07b nn-g">';
			foreach ( $p as $i => $post ) {
				$sum = $img ? '' : 'sum-d sum-t';
				if ( 0 === $i ) {
					$sum = trim( $sum . ' sum-m' );
				}
				$h .= '<li class="' . ( $eight ? 'd4' : 'd3' ) . ' t4">' . nn_card(
					$post,
					array(
						'size'  => 'm',
						'img'   => $img,
						'sum'   => $sum,
						'cls'   => $i ? 'as-s-m' : '',
						'sizes' => nn_sizes( $eight ? 384 : 282, 'calc(50vw - 44px)', $i ? '80px' : 'calc(100vw - 40px)' ),
					)
				) . '</li>';
			}
			$h .= '</ul>';
		} else {
			$h .= '<div class="nn-m07c">';
			$h .= '<div class="nn-m07c__lead">' . nn_card(
				$p[0],
				array(
					'size'  => 'l',
					'img'   => $img,
					'sum'   => 'sum',
					'sizes' => nn_sizes( $eight ? 384 : 588 ),
				)
			) . '</div>';
			$med = array_slice( $p, 1, 2 );
			if ( $med ) {
				$h .= '<div class="nn-m07c__med">';
				foreach ( $med as $m ) {
					$h .= nn_card(
						$m,
						array(
							'size'  => 'm',
							'img'   => $img,
							'sum'   => ( $eight || ! $img ) ? 'sum-d sum-t' : 'sum-t',
							'cls'   => 'as-s-m',
							'sizes' => nn_sizes( $eight ? 384 : 282, 'calc(50vw - 44px)', '80px' ),
						)
					);
				}
				$h .= '</div>';
			}
			$h .= nn_card_list(
				array_slice( $p, 3, 3 ),
				array(
					'size'  => 's',
					'img'   => $img,
					'thumb' => 'th-m',
					'sizes' => '80px',
				),
				array( 'class' => 'nn-m07c__list' )
			);
			$h .= '</div>';
		}
		return $h . '</section>';
	}

	// D: two categories side by side. E: three categories as headline columns.
	$n     = ( 'd' === $v ) ? 2 : 3;
	$cats  = nn_pick_categories( (array) $a['categories'], $n );
	$parts = '';
	$names = array();
	foreach ( $cats as $cid ) {
		$p = nn_module_posts( $a, ( 'd' === $v ) ? 3 : 5, $img, $cid );
		if ( ! $p ) {
			continue;
		}
		$name    = get_cat_name( $cid );
		$names[] = $name;
		$url     = $a['showLink'] ? get_category_link( $cid ) : '';
		if ( 'd' === $v ) {
			$parts .= '<div class="nn-m07d__half ' . ( $eight ? 'd4' : 'd6' ) . ' t4">' . nn_heading( $name, $url );
			$parts .= nn_card(
				$p[0],
				array(
					'size'  => 'l',
					'img'   => $img,
					'sum'   => $img ? 'sum-m' : 'sum-d sum-t sum-m',
					'cls'   => 'to-m-below',
					'sizes' => nn_sizes( $eight ? 384 : 588, 'calc(50vw - 44px)' ),
				)
			);
			$parts .= nn_card_list( array_slice( $p, 1, 2 ), array( 'size' => 'h' ), array( 'class' => 'nn-list--rule' ) );
			$parts .= '</div>';
		} else {
			$parts .= '<div class="nn-m07e__col d4">' . nn_heading( $name, $url );
			$parts .= nn_split_list( $p, 3, $img ? array( 'size' => 's', 'img' => true, 'thumb' => 'th-d th-t th-m', 'sizes' => '112px' ) : array( 'size' => 'h' ), array( 'm' => 4 ) );
			$parts .= '</div>';
		}
	}
	if ( '' === $parts ) {
		return nn_module_empty( 'M07' );
	}
	return nn_module_open( $base, $a, implode( ' · ', $names ) ) . '<div class="nn-m07' . $v . ' nn-g">' . $parts . '</div></section>';
}

/* ---------------------------------------------------------------- M08 / M09 */

/**
 * [M08] Lead + rail combo wrapper.
 *
 * @param array  $a       Attributes.
 * @param string $content Slots.
 * @return string
 */
function nn_render_lead_rail( $a, $content = '' ) {
	if ( '' === trim( $content ) ) {
		return '';
	}
	$cls = 'nn-module nn-m08 nn-g' . ( ! empty( $a['className'] ) ? ' ' . $a['className'] : '' );
	return '<div class="' . esc_attr( $cls ) . '">' . $content . '</div>';
}

/**
 * [M08] Slot: main (8 columns) or rail (4 columns).
 *
 * @param array  $a       Attributes.
 * @param string $content Modules.
 * @return string
 */
function nn_render_slot( $a, $content = '' ) {
	if ( '' === trim( $content ) ) {
		return '';
	}
	if ( isset( $a['area'] ) && 'rail' === $a['area'] ) {
		return '<aside class="nn-m08__rail d4">' . $content . '</aside>';
	}
	return '<div class="nn-m08__main d8">' . $content . '</div>';
}

/**
 * [M09] Section heading block.
 *
 * @param array $a Attributes.
 * @return string
 */
function nn_render_section_heading( $a ) {
	$a = wp_parse_args(
		(array) $a,
		array(
			'title'   => 'সর্বশেষ',
			'linkUrl' => '',
		)
	);
	return '<div class="nn-module nn-m09' . ( ! empty( $a['className'] ) ? ' ' . esc_attr( $a['className'] ) : '' ) . '">' . nn_heading( $a['title'], $a['linkUrl'] ) . '</div>';
}

/**
 * Inline "আরও পড়ুন" link inside an article body.
 *
 * @param array $a Attributes.
 * @return string
 */
function nn_render_inline_related( $a ) {
	$a    = wp_parse_args(
		(array) $a,
		array(
			'postId' => 0,
			'label'  => 'আরও পড়ুন',
		)
	);
	$post = $a['postId'] ? get_post( (int) $a['postId'] ) : null;
	if ( ! $post || 'publish' !== $post->post_status ) {
		return nn_is_editor_preview() ? '<div class="nn-empty">' . esc_html__( 'আরও পড়ুন: একটি খবর বেছে নিন।', 'narayanganj-news' ) . '</div>' : '';
	}
	return '<aside class="nn-inline-related"><span class="nn-inline-related__label">' . esc_html( $a['label'] ) . '</span><a class="nn-inline-related__link" href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( wp_strip_all_tags( get_the_title( $post ) ) ) . '</a></aside>';
}
