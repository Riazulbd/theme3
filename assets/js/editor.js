/**
 * Narayanganj News: block editor.
 * - Registers the module blocks (definitions come from PHP: window.nnEditor.blocks).
 *   Each preview is rendered by the server, so it matches the site.
 * - "Article display" panel: photo / text-only version and the source line.
 * - Removes video and embed blocks (Riaz: no video).
 * No build step: plain ES2017 with wp.element.createElement.
 */
(function (wp, cfg) {
	'use strict';
	if (!wp || !cfg) { return; }

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var be = wp.blockEditor;
	var c = wp.components;
	var useSelect = wp.data.useSelect;
	var SSR = wp.serverSideRender;
	var decode = (wp.htmlEntities && wp.htmlEntities.decodeEntities) || function (s) { return s; };

	var IMAGE = [
		{ value: 'default', label: 'সাইটের ডিফল্ট (site default)' },
		{ value: 'on', label: 'ছবিসহ (photos)' },
		{ value: 'off', label: 'শুধু লেখা (text-only)' }
	];
	var SOURCE = [
		{ value: 'latest', label: 'সর্বশেষ খবর (newest stories)' },
		{ value: 'category', label: 'একটি বিভাগ থেকে (one category)' },
		{ value: 'manual', label: 'নিজে বাছাই (pick stories; the rest auto-fills)' }
	];

	/* ---------------------------------------------------------- data hooks */
	function useCategories() {
		return useSelect(function (select) {
			return select('core').getEntityRecords('taxonomy', 'category', { per_page: 100, hide_empty: false, _fields: 'id,name,parent' });
		}, []);
	}

	function categoryOptions(cats, emptyLabel) {
		var opts = [{ value: 0, label: emptyLabel || '— স্বয়ংক্রিয় (auto: busiest unused section) —' }];
		(cats || []).forEach(function (t) {
			opts.push({ value: t.id, label: (t.parent ? '— ' : '') + decode(t.name) });
		});
		return opts;
	}

	/* ---------------------------------------------------------- post picker */
	function PostPicker(props) {
		var value = props.value || [];
		var max = props.max || 12;
		var st = useState('');
		var search = st[0];
		var setSearch = st[1];

		var found = useSelect(function (select) {
			if (search.length < 2) { return null; }
			return select('core').getEntityRecords('postType', 'post', { search: search, per_page: 8, status: 'publish', _fields: 'id,title' });
		}, [search]);
		var picked = useSelect(function (select) {
			if (!value.length) { return []; }
			return select('core').getEntityRecords('postType', 'post', { include: value, per_page: value.length, orderby: 'include', _fields: 'id,title' });
		}, [value.join(',')]);

		function title(p) { return decode((p.title && (p.title.rendered || p.title.raw)) || ('#' + p.id)); }
		function move(i, dir) {
			var next = value.slice();
			var j = i + dir;
			if (j < 0 || j >= next.length) { return; }
			var tmp = next[i]; next[i] = next[j]; next[j] = tmp;
			props.onChange(next);
		}

		var rows = (picked || []).map(function (p, i) {
			return el('div', { key: p.id, className: 'nn-picker__row' },
				el('span', { className: 'nn-picker__title' }, (i + 1) + '. ' + title(p)),
				el(c.Button, { icon: 'arrow-up-alt2', label: 'উপরে', size: 'small', onClick: function () { move(i, -1); } }),
				el(c.Button, { icon: 'arrow-down-alt2', label: 'নিচে', size: 'small', onClick: function () { move(i, 1); } }),
				el(c.Button, { icon: 'no-alt', label: 'সরান', size: 'small', isDestructive: true, onClick: function () {
					props.onChange(value.filter(function (id) { return id !== p.id; }));
				} })
			);
		});

		var results = (found || []).filter(function (p) { return value.indexOf(p.id) === -1; }).map(function (p) {
			return el(c.Button, { key: p.id, variant: 'tertiary', className: 'nn-picker__result', disabled: value.length >= max, onClick: function () {
				props.onChange(value.concat([p.id]));
				setSearch('');
			} }, '+ ' + title(p));
		});

		return el('div', { className: 'nn-picker' },
			rows.length ? el('div', { className: 'nn-picker__list' }, rows) : null,
			el(c.TextControl, { label: props.label || 'খবর খুঁজে যোগ করুন (search stories)', value: search, onChange: setSearch, __nextHasNoMarginBottom: true }),
			found === null && search.length >= 2 ? el(c.Spinner) : null,
			results.length ? el('div', { className: 'nn-picker__results' }, results) : null
		);
	}

	/* ---------------------------------------------------------- inspector controls */
	function variantOptions(def) {
		return (def.variations || []).map(function (v) { return { value: v[0], label: v[1] }; });
	}

	function Controls(props) {
		var def = props.def;
		var a = props.attributes;
		var set = props.setAttributes;
		var cats = useCategories();
		var want = def.controls || [];
		var out = [];
		var has = function (k) { return want.indexOf(k) > -1; };

		if (has('variant') && def.variations && def.variations.length) {
			out.push(el(c.SelectControl, { key: 'variant', label: 'লেআউট (layout)', value: a.variant, options: variantOptions(def), onChange: function (v) { set({ variant: v }); } }));
		}
		if (has('count') && props.name === 'nn/most-read') {
			out.push(el(c.SelectControl, { key: 'count', label: 'কতটি (items)', value: String(a.count), options: [{ value: '5', label: '১–৫' }, { value: '10', label: '১–১০' }], onChange: function (v) { set({ count: parseInt(v, 10) }); } }));
		} else if (has('count') && a.variant !== 'row') {
			out.push(el(c.RangeControl, { key: 'count', label: 'কতটি শিরোনাম (headlines, sidebar)', value: a.count, min: 4, max: 12, onChange: function (v) { set({ count: v }); } }));
		}
		if (has('image') && !(props.name === 'nn/lead-story' && a.variant === 'c') && !(props.name === 'nn/category-block' && a.variant === 'e')) {
			out.push(el(c.SelectControl, { key: 'image', label: 'ছবি (photos)', value: a.image, options: IMAGE, onChange: function (v) { set({ image: v }); } }));
		}
		if (has('title')) {
			out.push(el(c.TextControl, { key: 'title', label: 'শিরোনাম (title; empty = default / section name)', value: a.title || '', onChange: function (v) { set({ title: v }); } }));
		}
		if (has('link')) {
			out.push(el(c.ToggleControl, { key: 'link', label: '"আরও দেখুন" লিংক দেখান (show link)', checked: !!a.showLink, onChange: function (v) { set({ showLink: v }); } }));
			if (a.showLink && props.name === 'nn/latest') {
				out.push(el(c.TextControl, { key: 'linkUrl', label: 'লিংকের ঠিকানা (link URL; empty = posts page)', value: a.linkUrl || '', type: 'url', onChange: function (v) { set({ linkUrl: v }); } }));
			}
		}
		if (has('linkUrl')) {
			out.push(el(c.TextControl, { key: 'linkUrl', label: '"আরও দেখুন" ঠিকানা (link URL; empty = title only)', value: a.linkUrl || '', type: 'url', onChange: function (v) { set({ linkUrl: v }); } }));
		}
		if (has('categories')) {
			if (['d', 'e'].indexOf(a.variant) > -1) {
				var n = a.variant === 'd' ? 2 : 3;
				for (var i = 0; i < n; i++) {
					(function (idx) {
						var list = (a.categories || []).slice();
						out.push(el(c.SelectControl, { key: 'cat' + idx, label: 'বিভাগ ' + (idx + 1) + ' (section)', value: list[idx] || 0, options: categoryOptions(cats), onChange: function (v) {
							var next = (a.categories || []).slice();
							while (next.length < n) { next.push(0); }
							next[idx] = parseInt(v, 10) || 0;
							set({ categories: next });
						} }));
					}(i));
				}
			} else {
				out.push(el(c.SelectControl, { key: 'cat', label: 'বিভাগ (section)', value: a.category || 0, options: categoryOptions(cats), onChange: function (v) { set({ category: parseInt(v, 10) || 0 }); } }));
			}
		}
		if (has('source')) {
			var sourceOpts = props.name === 'nn/most-read' ? [{ value: 'popular', label: 'সবচেয়ে বেশি পড়া (most views)' }].concat(SOURCE) : SOURCE;
			out.push(el(c.SelectControl, { key: 'source', label: 'খবরের উৎস (stories from)', value: a.source, options: sourceOpts, onChange: function (v) { set({ source: v }); } }));
			if (a.source === 'category') {
				out.push(el(c.SelectControl, { key: 'scat', label: 'বিভাগ (section)', value: a.category || 0, options: categoryOptions(cats, '— বেছে নিন —'), onChange: function (v) { set({ category: parseInt(v, 10) || 0 }); } }));
			}
			if (a.source === 'manual') {
				out.push(el(PostPicker, { key: 'posts', value: a.posts || [], onChange: function (v) { set({ posts: v }); } }));
			}
			if (a.source !== 'manual' && a.source !== 'popular') {
				out.push(el(c.RangeControl, { key: 'offset', label: 'প্রথম কয়টি বাদ (skip newest)', value: a.offset || 0, min: 0, max: 20, onChange: function (v) { set({ offset: v || 0 }); } }));
			}
		}
		if (has('source') || has('dedupe')) {
			out.push(el(c.ToggleControl, { key: 'dedupe', label: 'উপরে দেখানো খবর বাদ দিন (skip stories already on the page)', checked: a.dedupe !== false, onChange: function (v) { set({ dedupe: v }); } }));
		}
		if (has('postId')) {
			out.push(el(PostPicker, { key: 'pid', max: 1, label: 'খবর বেছে নিন (pick a story)', value: a.postId ? [a.postId] : [], onChange: function (v) { set({ postId: v.length ? v[v.length - 1] : 0 }); } }));
		}
		if (!out.length) { return null; }
		return el(be.InspectorControls, null, el(c.PanelBody, { title: 'মডিউল সেটিংস (module settings)', initialOpen: true }, out));
	}

	/* ---------------------------------------------------------- registration */
	function preview(name, attributes) {
		return el(c.Disabled, null, el(SSR, {
			block: name,
			attributes: attributes,
			EmptyResponsePlaceholder: function () {
				return el('div', { className: 'nn-empty' }, 'এই মডিউলে এখন কোনো খবর নেই। (No stories yet.)');
			}
		}));
	}

	Object.keys(cfg.blocks || {}).forEach(function (name) {
		var def = cfg.blocks[name];
		var settings = {
			apiVersion: 3,
			title: def.title,
			description: def.description,
			category: def.category || 'nn-modules',
			icon: def.icon,
			attributes: def.attributes,
			supports: { html: false, customClassName: true },
			save: function () { return null; }
		};
		if (def.parent) {
			settings.parent = def.parent;
			settings.supports.inserter = false;
		}
		if (def.provides_context) { settings.providesContext = def.provides_context; }
		settings.usesContext = ['nn/area'];

		if (def.variations && def.variations.length) {
			settings.variations = def.variations.map(function (v, i) {
				return { name: name.replace('nn/', '') + '-' + v[0], title: v[1], attributes: { variant: v[0] }, isDefault: i === 0, scope: ['inserter', 'transform'], isActive: ['variant'] };
			});
		}

		if (name === 'nn/lead-rail') {
			settings.edit = function () {
				var bp = be.useBlockProps({ className: 'nn-ed-rail' });
				var ib = be.useInnerBlocksProps(bp, {
					template: [['nn/slot', { area: 'main' }], ['nn/slot', { area: 'rail' }]],
					templateLock: 'all',
					allowedBlocks: ['nn/slot']
				});
				return el('div', ib);
			};
			settings.save = function () { return el(be.InnerBlocks.Content); };
		} else if (name === 'nn/slot') {
			settings.edit = function (props) {
				var rail = props.attributes.area === 'rail';
				var bp = be.useBlockProps({ className: 'nn-ed-slot nn-ed-slot--' + (rail ? 'rail' : 'main'), 'data-label': rail ? 'রেল · ৪ কলাম (rail)' : 'মূল · ৮ কলাম (main)' });
				var ib = be.useInnerBlocksProps(bp, {
					templateLock: false,
					template: rail ? [['nn/latest', { variant: 'sidebar' }]] : [['nn/category-block', { variant: 'a' }]],
					renderAppender: be.InnerBlocks.ButtonBlockAppender
				});
				return el('div', ib);
			};
			settings.save = function () { return el(be.InnerBlocks.Content); };
		} else {
			settings.edit = function (props) {
				var bp = be.useBlockProps({ className: 'nn-ed-module' });
				var attrs = Object.assign({}, props.attributes);
				return el(Fragment, null,
					el(Controls, { def: def, name: name, attributes: props.attributes, setAttributes: props.setAttributes }),
					el('div', bp, preview(name, attrs))
				);
			};
		}
		if (!wp.blocks.getBlockType(name)) {
			wp.blocks.registerBlockType(name, settings);
		}
	});

	/* ---------------------------------------------------------- no video / embeds */
	wp.domReady(function () {
		(cfg.disallowed || []).forEach(function (name) {
			if (wp.blocks.getBlockType(name)) { wp.blocks.unregisterBlockType(name); }
		});
	});

	/* ---------------------------------------------------------- article panel */
	var Panel = (wp.editor && wp.editor.PluginDocumentSettingPanel) || (wp.editPost && wp.editPost.PluginDocumentSettingPanel);
	if (Panel && wp.plugins && wp.coreData) {
		wp.plugins.registerPlugin('nn-article', {
			render: function () {
				var postType = useSelect(function (s) { return s('core/editor').getCurrentPostType(); }, []);
				var entity = wp.coreData.useEntityProp('postType', postType || 'post', 'meta');
				var meta = entity[0] || {};
				var setMeta = entity[1];
				if (postType !== 'post') { return null; }
				return el(Panel, { name: 'nn-article', title: 'প্রতিবেদন প্রদর্শন (Article display)' },
					el(c.SelectControl, {
						label: 'ছবি (photo)',
						value: meta._nn_image_mode || '',
						options: [
							{ value: '', label: 'সাইটের ডিফল্ট (site default)' },
							{ value: 'on', label: 'ছবিসহ (with photo)' },
							{ value: 'off', label: 'শুধু লেখা (text-only version)' }
						],
						onChange: function (v) { setMeta(Object.assign({}, meta, { _nn_image_mode: v })); }
					}),
					el(c.TextControl, {
						label: 'সূত্র (source line, shown as "সূত্র: …")',
						value: meta._nn_source || '',
						onChange: function (v) { setMeta(Object.assign({}, meta, { _nn_source: v })); }
					}),
					el('p', { className: 'nn-ed-help' }, 'সারাংশ (deck) = Excerpt। বিভাগ, তারিখ বা লেখকের নাম সাইটে দেখানো হয় না।')
				);
			}
		});
	}
}(window.wp, window.nnEditor));
