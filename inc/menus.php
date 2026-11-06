<?php
/**
 * Menu trees for the nav bar, mobile scroller, drawer and footer.
 * Uses the assigned menu; falls back to top-level categories so a fresh
 * install already has a working nav.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Items of an assigned menu as a two-level tree.
 *
 * @param string $location Menu location.
 * @return array|null Null when no menu is assigned.
 */
function nn_menu_tree( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return null;
	}
	$menu = (int) $locations[ $location ];
	// The static tree (title, url, children) is cached; "active" is worked out per request.
	$tree = nn_remember(
		'menu|' . $menu,
		static function () use ( $menu ) {
			$items = wp_get_nav_menu_items( $menu, array( 'update_post_term_cache' => false ) );
			if ( ! $items ) {
				return array();
			}
			$nodes = array();
			$top   = array();
			foreach ( $items as $it ) {
				$nodes[ $it->ID ] = array(
					'title'    => $it->title,
					'url'      => $it->url,
					'children' => array(),
				);
				if ( ! (int) $it->menu_item_parent ) {
					$top[] = $it->ID;
				}
			}
			foreach ( $items as $it ) {
				$parent = (int) $it->menu_item_parent;
				if ( $parent && isset( $nodes[ $parent ] ) && in_array( $parent, $top, true ) ) {
					$nodes[ $parent ]['children'][] = $nodes[ $it->ID ];
				}
			}
			$out = array();
			foreach ( $top as $id ) {
				$out[] = $nodes[ $id ];
			}
			return $out;
		}
	);
	if ( ! $tree ) {
		return null;
	}
	return nn_mark_active( $tree );
}

/**
 * URLs that count as "here": this page, the story's sections and their parents.
 *
 * @return string[] Normalised URLs.
 */
function nn_current_urls() {
	static $urls = null;
	if ( null !== $urls ) {
		return $urls;
	}
	$urls = array();
	$add  = static function ( $u ) use ( &$urls ) {
		if ( $u && ! is_wp_error( $u ) ) {
			$urls[] = untrailingslashit( preg_replace( '/[?#].*$/', '', $u ) );
		}
	};
	if ( is_front_page() ) {
		$add( home_url( '/' ) );
	} elseif ( is_category() ) {
		$id = (int) get_queried_object_id();
		$add( get_category_link( $id ) );
		foreach ( get_ancestors( $id, 'category' ) as $a ) {
			$add( get_category_link( $a ) );
		}
	} elseif ( is_singular( 'post' ) ) {
		foreach ( wp_get_post_categories( get_queried_object_id() ) as $cid ) {
			$add( get_category_link( $cid ) );
			foreach ( get_ancestors( $cid, 'category' ) as $a ) {
				$add( get_category_link( $a ) );
			}
		}
		$add( get_permalink( get_queried_object_id() ) );
	} elseif ( is_singular() ) {
		$add( get_permalink( get_queried_object_id() ) );
	}
	return $urls;
}

/**
 * Add the "active" flag to a cached tree.
 *
 * @param array $tree Tree.
 * @return array
 */
function nn_mark_active( $tree ) {
	$here = nn_current_urls();
	foreach ( $tree as &$n ) {
		$n['active'] = in_array( untrailingslashit( preg_replace( '/[?#].*$/', '', (string) $n['url'] ) ), $here, true );
		foreach ( $n['children'] as &$c ) {
			$c['active'] = in_array( untrailingslashit( preg_replace( '/[?#].*$/', '', (string) $c['url'] ) ), $here, true );
			if ( $c['active'] ) {
				$n['active'] = true;
			}
		}
		unset( $c );
	}
	unset( $n );
	return nn_single_active( $tree );
}

/**
 * Fallback: top-level categories (most used first) with their children.
 *
 * @return array
 */
function nn_category_tree() {
	$base = nn_remember(
		'cat-tree',
		static function () {
			$terms = get_terms(
				array(
					'taxonomy'   => 'category',
					'hide_empty' => true,
					'orderby'    => 'count',
					'order'      => 'DESC',
					'number'     => 100,
				)
			);
			if ( is_wp_error( $terms ) || ! $terms ) {
				return array();
			}
			$parents = array();
			$kids    = array();
			foreach ( $terms as $t ) {
				if ( 'uncategorized' === $t->slug ) {
					continue;
				}
				$node = array(
					'title'    => $t->name,
					'url'      => get_term_link( $t ),
					'children' => array(),
					'id'       => (int) $t->term_id,
				);
				if ( $t->parent ) {
					$kids[ (int) $t->parent ][] = $node;
				} else {
					$parents[] = $node;
				}
			}
			foreach ( $parents as &$p ) {
				if ( isset( $kids[ $p['id'] ] ) ) {
					$p['children'] = $kids[ $p['id'] ];
				}
			}
			unset( $p );
			return $parents;
		}
	);
	if ( ! $base ) {
		return array();
	}
	return nn_mark_active( array_slice( $base, 0, (int) apply_filters( 'nn_fallback_nav_count', 12 ) ) );
}

/**
 * Keep one active top-level item (a story can sit in several sections).
 *
 * @param array $tree Tree.
 * @return array
 */
function nn_single_active( $tree ) {
	$seen = false;
	foreach ( $tree as &$n ) {
		if ( $n['active'] && $seen ) {
			$n['active'] = false;
		}
		$seen = $seen || $n['active'];
	}
	unset( $n );
	return $tree;
}

/**
 * Nav tree for a location with fallbacks (drawer/footer → primary → categories).
 *
 * @param string $location Location.
 * @return array
 */
function nn_nav_items( $location = 'primary' ) {
	static $cache = array();
	if ( isset( $cache[ $location ] ) ) {
		return $cache[ $location ];
	}
	$tree = nn_menu_tree( $location );
	if ( null === $tree && 'primary' !== $location && 'legal' !== $location ) {
		$tree = nn_menu_tree( 'primary' );
	}
	if ( null === $tree ) {
		$tree = ( 'legal' === $location ) ? nn_legal_fallback() : nn_category_tree();
	}
	$cache[ $location ] = apply_filters( 'nn_nav_items', $tree, $location );
	return $cache[ $location ];
}

/**
 * Legal links fallback: the privacy policy page, if set.
 *
 * @return array
 */
function nn_legal_fallback() {
	$out = array();
	$url = get_privacy_policy_url();
	if ( $url ) {
		$out[] = array(
			'title'    => 'গোপনীয়তা নীতি',
			'url'      => $url,
			'active'   => false,
			'children' => array(),
		);
	}
	return $out;
}

/**
 * One nav link.
 *
 * @param array  $n     Node.
 * @param string $class Class.
 * @return string
 */
function nn_nav_link( $n, $class = 'nn-nav__link' ) {
	$cur = $n['active'] ? ' aria-current="page"' : '';
	return '<a class="' . esc_attr( $class . ( $n['active'] ? ' is-active' : '' ) ) . '" href="' . esc_url( $n['url'] ) . '"' . $cur . '>' . esc_html( $n['title'] ) . '</a>';
}

/**
 * Legal links list.
 *
 * @param string $class List class.
 * @return string
 */
function nn_legal_html( $class = 'nn-legal' ) {
	$items = nn_nav_items( 'legal' );
	if ( ! $items ) {
		return '';
	}
	$h = '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $n ) {
		$h .= '<li>' . nn_nav_link( $n, 'nn-legal__link' ) . '</li>';
	}
	return $h . '</ul>';
}
