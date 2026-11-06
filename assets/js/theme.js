/**
 * Narayanganj News: front-end behaviour (vanilla JS, deferred, no jQuery).
 * Drawer + accordion, search (inline / sheet / suggestions), "আরও" dropdown,
 * sticky-condensed header, copy-link toast, sticky article rail, view beacon.
 */
(function () {
	'use strict';

	var d = document;
	var html = d.documentElement;
	var cfg = window.nnData || {};
	var desktop = window.matchMedia('(min-width: 1024px)');
	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';

	function qs(sel, ctx) { return (ctx || d).querySelector(sel); }
	function qsa(sel, ctx) { return Array.prototype.slice.call((ctx || d).querySelectorAll(sel)); }
	function visible(el) { return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length); }

	function trapFocus(container, e) {
		if (e.key !== 'Tab') { return; }
		var items = qsa(FOCUSABLE, container).filter(visible);
		if (!items.length) { return; }
		var first = items[0];
		var last = items[items.length - 1];
		if (e.shiftKey && d.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && d.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	}

	/* ---------------------------------------------------------- drawer */
	var drawer = qs('#nn-drawer');
	var drawerReturn = null;
	var drawerTimer = null;

	function setExpanded(sel, value) {
		qsa(sel).forEach(function (b) { b.setAttribute('aria-expanded', value ? 'true' : 'false'); });
	}

	function openDrawer(trigger) {
		if (!drawer) { return; }
		closeSearch(false);
		clearTimeout(drawerTimer);
		drawerReturn = trigger || d.activeElement;
		drawer.hidden = false;
		html.classList.add('nn-locked');
		setExpanded('[data-nn-drawer-open]', true);
		window.requestAnimationFrame(function () {
			drawer.classList.add('is-open');
			var panel = qs('.nn-drawer__panel', drawer);
			if (panel) { panel.focus(); }
		});
	}

	function closeDrawer() {
		if (!drawer || drawer.hidden) { return; }
		drawer.classList.remove('is-open');
		html.classList.remove('nn-locked');
		setExpanded('[data-nn-drawer-open]', false);
		drawerTimer = setTimeout(function () { drawer.hidden = true; }, reduced.matches ? 0 : 240);
		if (drawerReturn && drawerReturn.focus) { drawerReturn.focus(); }
	}

	d.addEventListener('click', function (e) {
		var t = e.target;
		var open = t.closest('[data-nn-drawer-open]');
		if (open) { e.preventDefault(); openDrawer(open); return; }
		if (t.closest('[data-nn-drawer-close]')) { e.preventDefault(); closeDrawer(); return; }

		var acc = t.closest('[data-nn-acc]');
		if (acc) {
			var sub = d.getElementById(acc.getAttribute('aria-controls'));
			var on = acc.getAttribute('aria-expanded') !== 'true';
			acc.setAttribute('aria-expanded', on ? 'true' : 'false');
			if (sub) { sub.hidden = !on; }
			var li = acc.closest('li');
			if (li) { li.classList.toggle('is-open', on); }
			return;
		}

		var more = t.closest('[data-nn-more]');
		if (more) { toggleMore(more); return; }
		if (!t.closest('.nn-nav__item--more')) { closeMore(); }

		var so = t.closest('[data-nn-search-open]');
		if (so) { e.preventDefault(); openSearch(so); return; }
		if (t.closest('[data-nn-search-close]')) { e.preventDefault(); closeSearch(true); return; }

		var copy = t.closest('[data-nn-copy]');
		if (copy) { e.preventDefault(); copyLink(copy); return; }

		var mode = t.closest('[data-nn-mode]');
		if (mode) { e.preventDefault(); if (window.nnSetMode) { window.nnSetMode(); } return; }

		var tick = t.closest('[data-nn-ticker]');
		if (tick) {
			e.preventDefault();
			var box = tick.closest('.nn-ticker');
			var off = !box.classList.contains('is-paused');
			box.classList.toggle('is-paused', off);
			tick.setAttribute('aria-pressed', off ? 'true' : 'false');
			tick.setAttribute('aria-label', tick.getAttribute(off ? 'data-play' : 'data-pause'));
		}
	});

	if (drawer) {
		drawer.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { closeDrawer(); return; }
			trapFocus(qs('.nn-drawer__panel', drawer), e);
		});
	}

	/* ---------------------------------------------------------- "আরও" dropdown */
	function toggleMore(btn) {
		var menu = d.getElementById(btn.getAttribute('aria-controls'));
		var on = btn.getAttribute('aria-expanded') !== 'true';
		btn.setAttribute('aria-expanded', on ? 'true' : 'false');
		if (menu) { menu.hidden = !on; }
	}
	function closeMore() {
		qsa('[data-nn-more][aria-expanded="true"]').forEach(toggleMore);
	}

	/* ---------------------------------------------------------- search */
	var search = qs('#nn-search');
	var searchReturn = null;
	var input = search ? qs('.nn-field__input', search) : null;
	var results = search ? qs('.nn-search__results', search) : null;
	var timer = null;
	var ctrl = null;

	function openSearch(trigger) {
		if (!search) { return; }
		closeDrawer();
		searchReturn = trigger;
		var floating = desktop.matches && html.classList.contains('nn-condensed-on');
		search.classList.toggle('is-floating', floating);
		search.hidden = false;
		html.classList.add('nn-search-open');
		if (!desktop.matches) { html.classList.add('nn-locked'); }
		setExpanded('[data-nn-search-open]', true);
		if (input) { input.focus(); }
	}

	function closeSearch(restore) {
		if (!search || search.hidden) { return; }
		search.hidden = true;
		search.classList.remove('is-floating');
		html.classList.remove('nn-search-open');
		if (!drawer || drawer.hidden) { html.classList.remove('nn-locked'); }
		setExpanded('[data-nn-search-open]', false);
		if (restore && searchReturn && visible(searchReturn)) { searchReturn.focus(); }
	}

	if (search) {
		search.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { closeSearch(true); return; }
			if (!desktop.matches) { trapFocus(search, e); }
		});
	}

	function esc(s) {
		var div = d.createElement('div');
		div.textContent = s;
		return div.innerHTML;
	}

	function suggest(q) {
		if (!results || !cfg.searchUrl || !window.fetch) { return; }
		if (ctrl && ctrl.abort) { ctrl.abort(); }
		ctrl = window.AbortController ? new AbortController() : null;
		var url = cfg.searchUrl + (cfg.searchUrl.indexOf('?') > -1 ? '&' : '?') +
			'search=' + encodeURIComponent(q) + '&per_page=3&type=post&subtype=post&_fields=title,url';
		fetch(url, ctrl ? { signal: ctrl.signal } : {})
			.then(function (r) { return r.ok ? r.json() : []; })
			.then(function (items) {
				if (!items.length) {
					results.innerHTML = '<p class="nn-search__label">' + esc(cfg.i18n.none) + '</p>';
					return;
				}
				var h = '<p class="nn-search__label">' + esc(cfg.i18n.suggest) + '</p><ul class="nn-list">';
				items.forEach(function (it) {
					var div = d.createElement('div');
					div.innerHTML = it.title;
					h += '<li><article class="nn-card nn-card--h"><h3 class="nn-card__title"><a href="' +
						esc(it.url) + '">' + esc(div.textContent) + '</a></h3></article></li>';
				});
				results.innerHTML = h + '</ul>';
			})
			.catch(function () {});
	}

	if (input) {
		input.addEventListener('input', function () {
			var q = input.value.trim();
			search.classList.toggle('has-query', q.length > 0);
			clearTimeout(timer);
			if (q.length < 2) { results.innerHTML = ''; return; }
			if (desktop.matches) { return; }
			timer = setTimeout(function () { suggest(q); }, 250);
		});
	}

	// Submit state: busy button, skeleton in the sheet.
	qsa('.nn-field').forEach(function (form) {
		form.addEventListener('submit', function (e) {
			var field = qs('.nn-field__input', form);
			if (field && !field.value.trim()) { e.preventDefault(); field.focus(); return; }
			form.classList.add('is-busy');
			var label = qs('.nn-field__label', form);
			if (label && label.getAttribute('data-busy')) {
				label.setAttribute('data-idle', label.textContent);
				label.textContent = label.getAttribute('data-busy');
			}
			if (results && search && search.contains(form)) {
				results.innerHTML = '<p class="nn-search__label">' + esc(cfg.i18n.searching) +
					'</p><div class="nn-skeleton" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span></div>';
			}
		});
	});
	// Back/forward cache: reset the busy state.
	window.addEventListener('pageshow', function () {
		qsa('.nn-field.is-busy').forEach(function (form) {
			form.classList.remove('is-busy');
			var label = qs('.nn-field__label', form);
			if (label && label.getAttribute('data-idle')) { label.textContent = label.getAttribute('data-idle'); }
		});
	});

	d.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { closeMore(); }
	});

	/* ---------------------------------------------------------- sticky header states */
	var condensed = qs('.nn-condensed');
	function setCondensed(on) {
		html.classList.toggle('nn-condensed-on', on);
		if (condensed) {
			condensed.inert = !on;
			condensed.setAttribute('aria-hidden', on ? 'false' : 'true');
		}
		if (!on && search && search.classList.contains('is-floating')) { closeSearch(false); }
	}
	if ('IntersectionObserver' in window) {
		var top = qs('.nn-sentinel--top');
		var after = qs('.nn-sentinel--header');
		if (top) {
			new IntersectionObserver(function (entries) {
				html.classList.toggle('nn-stuck', !entries[0].isIntersecting);
			}).observe(top);
		}
		if (after) {
			new IntersectionObserver(function (entries) {
				var e = entries[0];
				setCondensed(!e.isIntersecting && e.boundingClientRect.top < 0);
			}).observe(after);
		}
	}

	/* ---------------------------------------------------------- copy link + toast */
	var toast = qs('.nn-toast');
	var toastTimer = null;
	function showToast(btn) {
		if (btn) { btn.classList.add('is-copied'); }
		if (!toast) { return; }
		toast.hidden = false;
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () {
			toast.hidden = true;
			if (btn) { btn.classList.remove('is-copied'); }
		}, 2400);
	}
	function copyLink(btn) {
		var url = btn.getAttribute('data-nn-copy') || window.location.href;
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(url).then(function () { showToast(btn); }, function () { legacyCopy(url, btn); });
		} else {
			legacyCopy(url, btn);
		}
	}
	function legacyCopy(url, btn) {
		var ta = d.createElement('textarea');
		ta.value = url;
		ta.setAttribute('readonly', '');
		ta.style.position = 'fixed';
		ta.style.opacity = '0';
		d.body.appendChild(ta);
		ta.select();
		try { d.execCommand('copy'); showToast(btn); } catch (err) {}
		d.body.removeChild(ta);
		btn.focus();
	}

	/* ---------------------------------------------------------- sticky article rail */
	var rail = qs('.nn-single__rail');
	function placeRail() {
		if (!rail) { return; }
		if (!desktop.matches) { rail.style.top = ''; return; }
		var admin = parseInt(getComputedStyle(html).getPropertyValue('--wp-admin--admin-bar--height'), 10) || 0;
		var topGap = admin + 88;
		var fit = window.innerHeight - rail.offsetHeight - 24;
		rail.style.top = Math.min(topGap, fit) + 'px';
	}
	if (rail) {
		placeRail();
		window.addEventListener('resize', placeRail, { passive: true });
		window.addEventListener('load', placeRail);
	}

	/* ---------------------------------------------------------- prefetch on hover (browsers without speculation rules) */
	if (!(window.HTMLScriptElement && HTMLScriptElement.supports && HTMLScriptElement.supports('speculationrules')) &&
		!(navigator.connection && navigator.connection.saveData)) {
		var fetched = {};
		var count = 0;
		var prefetch = function (e) {
			var a = e.target.closest && e.target.closest('a[href]');
			if (!a || a.origin !== location.origin || a.target === '_blank' || a.hash && a.pathname === location.pathname) { return; }
			if (/\/wp-(admin|login|json)|[?&](s|preview)=/.test(a.href) || fetched[a.href] || count > 12) { return; }
			fetched[a.href] = 1;
			count++;
			var l = d.createElement('link');
			l.rel = 'prefetch';
			l.href = a.href;
			d.head.appendChild(l);
		};
		d.addEventListener('pointerover', function (e) { if (e.pointerType === 'mouse') { prefetch(e); } }, { passive: true });
		d.addEventListener('touchstart', prefetch, { passive: true });
	}

	/* ---------------------------------------------------------- most-read beacon */
	if (cfg.viewUrl && cfg.postId) {
		var key = 'nn_v_' + cfg.postId;
		try {
			if (!window.sessionStorage.getItem(key)) {
				window.sessionStorage.setItem(key, '1');
				if (navigator.sendBeacon) {
					navigator.sendBeacon(cfg.viewUrl);
				} else if (window.fetch) {
					fetch(cfg.viewUrl, { method: 'POST', keepalive: true, credentials: 'omit' });
				}
			}
		} catch (err) {}
	}
}());
