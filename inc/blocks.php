<?php
/**
 * Module blocks. Server-rendered (dynamic), so every module always shows the
 * latest stories and editors only choose layout, photos and source.
 * The editor UI lives in assets/js/editor.js and reads these definitions.
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block definitions: name => settings.
 *
 * @return array
 */
function nn_block_defs() {
	$image  = array(
		'type'    => 'string',
		'default' => 'default',
	);
	$source = array(
		'source'   => array(
			'type'    => 'string',
			'default' => 'latest',
		),
		'category' => array(
			'type'    => 'integer',
			'default' => 0,
		),
		'posts'    => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'integer' ),
		),
		'offset'   => array(
			'type'    => 'integer',
			'default' => 0,
		),
		'dedupe'   => array(
			'type'    => 'boolean',
			'default' => true,
		),
	);
	$common = array(
		'className' => array( 'type' => 'string' ),
		'anchor'    => array( 'type' => 'string' ),
	);
	$str    = static function ( $default ) {
		return array(
			'type'    => 'string',
			'default' => $default,
		);
	};

	$defs = array(
		'lead-story'      => array(
			'title'       => 'M03 · লিড খবর (Lead story)',
			'description' => 'Top of the home page. A: image lead + list · B: full-width image · C: text-only big headline.',
			'icon'        => 'cover-image',
			'render'      => 'nn_render_lead_story',
			'attributes'  => array(
				'variant' => $str( 'a' ),
				'image'   => $image,
			) + $source + $common,
			'controls'    => array( 'variant', 'image', 'source' ),
			'variations'  => array(
				array( 'a', 'M03-A · ছবি বাঁয়ে + তালিকা (image lead + list)' ),
				array( 'b', 'M03-B · পূর্ণ প্রস্থ ছবি (full-width image)' ),
				array( 'c', 'M03-C · শুধু লেখা (text-only)' ),
			),
		),
		'top-news'        => array(
			'title'       => 'M04 · শীর্ষ খবর (Top news grid)',
			'description' => '4-up, 3-up or 2-up story grid, with or without photos.',
			'icon'        => 'grid-view',
			'render'      => 'nn_render_top_news',
			'attributes'  => array(
				'variant' => $str( '4' ),
				'image'   => $image,
				'title'   => $str( '' ),
			) + $source + $common,
			'controls'    => array( 'variant', 'image', 'title', 'source' ),
			'variations'  => array(
				array( '4', 'M04 · ৪টি খবর (4-up)' ),
				array( '3', 'M04 · ৩টি খবর (3-up)' ),
				array( '2', 'M04 · ২টি খবর (2-up)' ),
			),
		),
		'latest'          => array(
			'title'       => 'M05 · দিনের সর্বশেষ (Latest)',
			'description' => 'Headline list, no timestamps. Sidebar (rail) or full-width row.',
			'icon'        => 'list-view',
			'render'      => 'nn_render_latest',
			'attributes'  => array(
				'variant'  => $str( 'sidebar' ),
				'title'    => $str( 'দিনের সর্বশেষ' ),
				'showLink' => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'linkUrl'  => $str( '' ),
				'count'    => array(
					'type'    => 'integer',
					'default' => 8,
				),
			) + $source + $common,
			'controls'    => array( 'variant', 'title', 'link', 'count', 'source' ),
			'variations'  => array(
				array( 'sidebar', 'M05 · সাইডবার তালিকা (sidebar)' ),
				array( 'row', 'M05 · সারি (horizontal row)' ),
			),
		),
		'most-read'       => array(
			'title'       => 'M06 · সর্বাধিক পঠিত (Most read)',
			'description' => 'Numbered list 1–5 or 1–10, Bengali digits.',
			'icon'        => 'editor-ol',
			'render'      => 'nn_render_most_read',
			'attributes'  => array(
				'count' => array(
					'type'    => 'integer',
					'default' => 5,
				),
				'title' => $str( 'সর্বাধিক পঠিত' ),
			) + array_merge(
				$source,
				array(
					'source' => $str( 'popular' ),
					'dedupe' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				)
			) + $common,
			'controls'    => array( 'count', 'title', 'source' ),
			'variations'  => array(),
		),
		'category-block'  => array(
			'title'       => 'M07 · বিভাগ ব্লক (Category block)',
			'description' => 'A: 1 large + 4 list · B: 4-up · C: 1 large + 2 medium + 3 list · D: 2 categories · E: 3 categories as headline columns.',
			'icon'        => 'category',
			'render'      => 'nn_render_category_block',
			'attributes'  => array(
				'variant'    => $str( 'a' ),
				'image'      => $image,
				'category'   => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'categories' => array(
					'type'    => 'array',
					'default' => array(),
					'items'   => array( 'type' => 'integer' ),
				),
				'title'      => $str( '' ),
				'showLink'   => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'dedupe'     => array(
					'type'    => 'boolean',
					'default' => true,
				),
			) + $common,
			'controls'    => array( 'variant', 'image', 'categories', 'title', 'link', 'dedupe' ),
			'variations'  => array(
				array( 'a', 'M07-A · ১টি বড় + ৪টি তালিকা' ),
				array( 'b', 'M07-B · ৪টি খবরের গ্রিড' ),
				array( 'c', 'M07-C · ১টি বড় + ২টি মাঝারি + ৩টি তালিকা' ),
				array( 'd', 'M07-D · পাশাপাশি ২টি বিভাগ' ),
				array( 'e', 'M07-E · ৩টি বিভাগের শিরোনাম কলাম' ),
			),
		),
		'lead-rail'       => array(
			'title'       => 'M08 · মূল + রেল (Lead + rail combo)',
			'description' => '8-column main slot + 4-column rail on desktop; stacks on tablet and mobile.',
			'icon'        => 'columns',
			'render'      => 'nn_render_lead_rail',
			'attributes'  => $common,
			'controls'    => array(),
			'variations'  => array(),
			'inner'       => true,
		),
		'slot'            => array(
			'title'            => 'M08 slot',
			'description'      => 'Main (8 columns) or rail (4 columns) slot of the lead + rail combo.',
			'icon'             => 'align-pull-left',
			'render'           => 'nn_render_slot',
			'attributes'       => array( 'area' => $str( 'main' ) ),
			'controls'         => array(),
			'variations'       => array(),
			'inner'            => true,
			'parent'           => array( 'nn/lead-rail' ),
			'provides_context' => array( 'nn/area' => 'area' ),
		),
		'section-heading' => array(
			'title'       => 'M09 · বিভাগ শিরোনাম (Section heading)',
			'description' => 'Rule with the brand tick, title and optional "আরও দেখুন".',
			'icon'        => 'heading',
			'render'      => 'nn_render_section_heading',
			'attributes'  => array(
				'title'   => $str( 'সর্বশেষ' ),
				'linkUrl' => $str( '' ),
			) + $common,
			'controls'    => array( 'title', 'linkUrl' ),
			'variations'  => array(),
		),
		'inline-related'  => array(
			'title'       => 'আরও পড়ুন (Inline related link)',
			'description' => 'A related story link inside the article body.',
			'icon'        => 'admin-links',
			'render'      => 'nn_render_inline_related',
			'attributes'  => array(
				'postId' => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'label'  => $str( 'আরও পড়ুন' ),
			) + $common,
			'controls'    => array( 'postId' ),
			'variations'  => array(),
			'category'    => 'text',
		),
	);
	return apply_filters( 'nn_block_defs', $defs );
}

/**
 * Register the blocks on the server.
 */
function nn_register_blocks() {
	foreach ( nn_block_defs() as $slug => $d ) {
		$args = array(
			'api_version'     => 3,
			'title'           => $d['title'],
			'description'     => $d['description'],
			'category'        => isset( $d['category'] ) ? $d['category'] : 'nn-modules',
			'icon'            => $d['icon'],
			'attributes'      => $d['attributes'],
			'render_callback' => $d['render'],
			'uses_context'    => array( 'nn/area' ),
			'supports'        => array(
				'html'            => false,
				'customClassName' => true,
				'reusable'        => true,
			),
		);
		if ( ! empty( $d['parent'] ) ) {
			$args['parent']             = $d['parent'];
			$args['supports']['inserter'] = false;
		}
		if ( ! empty( $d['provides_context'] ) ) {
			$args['provides_context'] = $d['provides_context'];
		}
		register_block_type( 'nn/' . $slug, $args );
	}
}
add_action( 'init', 'nn_register_blocks' );

/**
 * Definitions for the editor script (no PHP callables).
 *
 * @return array
 */
function nn_block_defs_for_js() {
	$out = array();
	foreach ( nn_block_defs() as $slug => $d ) {
		unset( $d['render'] );
		$out[ 'nn/' . $slug ] = $d;
	}
	return $out;
}
