/**
 * Share-image card, drawn in the browser so Bengali text is shaped correctly
 * with the site font. Five styles (Theme Settings → Share picture):
 *   classic  top band (logo + date), framed photo, headline on colour, bottom line
 *   dark     photo fading into dark, date tag, site-name ribbon, headline, links row
 *   light    photo on top, white panel, headline with the last line in colour
 *   band     photo on top, logo on photo, headline on a colour band, date + website
 *   strip    wide photo with a logo strip (no headline)
 */
(function () {
	'use strict';

	var d = document;
	var FONT = 'SolaimanLipi, system-ui, sans-serif';

	/* ------------------------------------------------------------ helpers */
	function rgb(hex) {
		hex = String(hex || '#000000').replace('#', '');
		if (hex.length === 3) { hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]; }
		return [parseInt(hex.substr(0, 2), 16), parseInt(hex.substr(2, 2), 16), parseInt(hex.substr(4, 2), 16)];
	}
	function mix(hex, other, t) {
		var a = rgb(hex);
		var b = rgb(other);
		return 'rgb(' + a.map(function (v, i) { return Math.round(v * (1 - t) + b[i] * t); }).join(',') + ')';
	}
	function alpha(hex, a) { return 'rgba(' + rgb(hex).join(',') + ',' + a + ')'; }
	function contrast(hex) {
		var l = rgb(hex).map(function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
		return (0.2126 * l[0] + 0.7152 * l[1] + 0.0722 * l[2]) > 0.35 ? '#111111' : '#ffffff';
	}
	function loadImg(src) {
		return new Promise(function (resolve) {
			if (!src) { resolve(null); return; }
			var i = new Image();
			i.crossOrigin = 'anonymous';
			i.onload = function () { resolve(i); };
			i.onerror = function () { resolve(null); };
			i.src = src;
		});
	}
	function rr(x, px, py, w, h, r) {
		x.beginPath();
		x.moveTo(px + r, py);
		x.arcTo(px + w, py, px + w, py + h, r);
		x.arcTo(px + w, py + h, px, py + h, r);
		x.arcTo(px, py + h, px, py, r);
		x.arcTo(px, py, px + w, py, r);
		x.closePath();
	}
	function cover(x, img, px, py, w, h) {
		var s = Math.max(w / img.width, h / img.height);
		var sw = w / s;
		var sh = h / s;
		x.drawImage(img, (img.width - sw) / 2, (img.height - sh) / 2, sw, sh, px, py, w, h);
	}
	function wrap(x, text, max) {
		var lines = [];
		var line = '';
		text.split(/\s+/).forEach(function (w) {
			var t = line ? line + ' ' + w : w;
			if (x.measureText(t).width > max && line) { lines.push(line); line = w; } else { line = t; }
		});
		if (line) { lines.push(line); }
		return lines;
	}
	// Largest size (down to min) at which the text fits maxLines inside the area.
	function fit(x, text, maxW, maxLines, area, size, min) {
		var lines;
		for (;;) {
			x.font = size + 'px ' + FONT;
			lines = wrap(x, text, maxW);
			if ((lines.length <= maxLines && lines.length * size * 1.4 <= area) || size <= min) { break; }
			size -= 2;
		}
		return { size: size, lines: lines, lh: size * 1.4 };
	}
	// Centred headline; colourOf(i, n) picks each line's colour.
	function headline(x, t, cx, top, area, colourOf) {
		var y = top + Math.max(0, (area - t.lines.length * t.lh) / 2);
		x.font = t.size + 'px ' + FONT;
		x.textAlign = 'center';
		x.textBaseline = 'top';
		x.lineWidth = Math.max(1, t.size / 48);
		t.lines.forEach(function (l, i) {
			var c = colourOf(i, t.lines.length);
			x.fillStyle = c;
			x.strokeStyle = c;
			x.fillText(l, cx, y + i * t.lh);
			x.strokeText(l, cx, y + i * t.lh);
		});
	}
	// Logo on a white rounded plate (works for logos with or without a background).
	function plate(x, logo, px, py, maxW, maxH, alignRight) {
		var h = maxH;
		var w = logo.width * h / logo.height;
		if (w > maxW) { w = maxW; h = logo.height * w / logo.width; }
		var pad = 12;
		var bx = alignRight ? px - w - 2 * pad : px;
		x.fillStyle = '#ffffff';
		rr(x, bx, py, w + 2 * pad, h + 2 * pad, 12);
		x.fill();
		x.drawImage(logo, bx + pad, py + pad, w, h);
	}
	function text(x, s, px, py, size, colour, align) {
		x.font = size + 'px ' + FONT;
		x.fillStyle = colour;
		x.textAlign = align || 'left';
		x.textBaseline = 'middle';
		x.fillText(s, px, py);
	}
	function noPhoto(x, w, h, col) {
		var g = x.createLinearGradient(0, 0, w, h);
		g.addColorStop(0, mix(col, '#000000', 0.15));
		g.addColorStop(1, mix(col, '#ffffff', 0.25));
		x.fillStyle = g;
		x.fillRect(0, 0, w, h);
	}

	/* ------------------------------------------------------------ styles */
	var STYLES = {
		classic: function (x, c, p) {
			var W = 1080;
			var H = 1080;
			var col = c.color;
			var g = x.createLinearGradient(0, 0, 0, H);
			g.addColorStop(0, mix(col, '#000000', 0.2));
			g.addColorStop(0.55, col);
			g.addColorStop(1, mix(col, '#000000', 0.35));
			x.fillStyle = g;
			x.fillRect(0, 0, W, H);
			var head = c.head || mix(col, '#ffffff', 0.9);
			x.fillStyle = head;
			x.fillRect(0, 0, W, 150);
			var headText = c.headText || contrast(c.head || '#fbeaea');
			if (p.logo) {
				var lh = 84;
				var lw = p.logo.width * lh / p.logo.height;
				if (lw > 440) { lw = 440; lh = p.logo.height * lw / p.logo.width; }
				x.drawImage(p.logo, 50, (150 - lh) / 2, lw, lh);
			} else {
				text(x, c.site, 50, 75, 54, headText);
			}
			text(x, c.date, W - 50, 75, 40, headText, 'right');
			var top = 190;
			if (p.photo) {
				x.fillStyle = '#ffffff';
				rr(x, 40, 130, 1000, 520, 40);
				x.fill();
				x.save();
				rr(x, 52, 142, 976, 496, 30);
				x.clip();
				cover(x, p.photo, 52, 142, 976, 496);
				x.restore();
				top = 680;
			}
			var area = H - 105 - top;
			var t = fit(x, c.title, 920, p.photo ? 4 : 8, area, p.photo ? 72 : 96, 32);
			var tc = c.text || contrast(col);
			headline(x, t, W / 2, top, area, function () { return tc; });
			x.fillStyle = c.bar || 'rgba(0,0,0,0.28)';
			x.fillRect(0, H - 85, W, 85);
			text(x, '«««    ' + c.foot + '    »»»', W / 2, H - 42, 36, c.barText || (c.bar ? contrast(c.bar) : '#ffffff'), 'center');
		},

		dark: function (x, c, p) {
			var W = 1080;
			var H = 1080;
			var bg = c.bar || '#0B1220';
			x.fillStyle = bg;
			x.fillRect(0, 0, W, H);
			if (p.photo) {
				cover(x, p.photo, 0, 0, W, 700);
			} else {
				noPhoto(x, W, 700, c.color);
			}
			var fade = x.createLinearGradient(0, 330, 0, 700);
			fade.addColorStop(0, alpha(bg, 0));
			fade.addColorStop(1, alpha(bg, 1));
			x.fillStyle = fade;
			x.fillRect(0, 330, W, 372);
			['left', 'right'].forEach(function (side) {
				var gx = side === 'left' ? x.createLinearGradient(0, 0, 140, 0) : x.createLinearGradient(W, 0, W - 140, 0);
				gx.addColorStop(0, alpha(bg, 0.85));
				gx.addColorStop(1, alpha(bg, 0));
				x.fillStyle = gx;
				x.fillRect(side === 'left' ? 0 : W - 140, 0, 140, 700);
			});
			// Date tag.
			x.font = '34px ' + FONT;
			var dw = x.measureText(c.date).width + 60;
			x.fillStyle = 'rgba(20,24,36,0.78)';
			x.fillRect(50, 60, dw, 64);
			x.fillStyle = c.color;
			x.fillRect(50, 60, 6, 64);
			text(x, c.date, 50 + dw / 2 + 3, 92, 34, c.headText || '#ffffff', 'center');
			if (p.logo) { plate(x, p.logo, W - 50, 50, 240, 84, true); }
			// Site-name ribbon (slanted).
			x.font = '46px ' + FONT;
			var rw = x.measureText(c.site).width + 120;
			var rx = (W - rw) / 2;
			x.fillStyle = c.color;
			x.beginPath();
			x.moveTo(rx + 24, 720);
			x.lineTo(rx + rw + 24, 720);
			x.lineTo(rx + rw - 24, 800);
			x.lineTo(rx - 24, 800);
			x.closePath();
			x.fill();
			x.fillStyle = mix(c.color, '#ffffff', 0.25);
			x.fillRect(rx + 40, 806, rw - 30, 5);
			text(x, c.site, W / 2, 760, 46, contrast(c.color), 'center');
			var t = fit(x, c.title, 900, 3, 170, 68, 34);
			var tc = c.text || '#ffffff';
			headline(x, t, W / 2, 835, 170, function () { return tc; });
			var bt = c.barText || 'rgba(255,255,255,0.85)';
			text(x, '●  ' + c.foot, 60, H - 50, 30, bt, 'left');
			if (c.host) { text(x, c.host, W - 60, H - 50, 30, bt, 'right'); }
		},

		light: function (x, c, p) {
			var W = 1080;
			var H = 1080;
			var panel = c.head || '#ffffff';
			x.fillStyle = panel;
			x.fillRect(0, 0, W, H);
			if (p.photo) {
				cover(x, p.photo, 0, 0, W, 740);
			} else {
				noPhoto(x, W, 740, c.color);
			}
			x.fillStyle = c.color;
			x.fillRect(0, 740, W, 8);
			if (p.logo) { plate(x, p.logo, W - 40, 40, 260, 84, true); }
			var t = fit(x, c.title, 960, 3, 270, 74, 34);
			var dark = c.text || contrast(panel);
			headline(x, t, W / 2, 768, 270, function (i, n) { return (n > 1 && i === n - 1) ? c.color : dark; });
			x.fillStyle = c.bar || c.color;
			x.fillRect(0, H - 22, W, 22);
		},

		band: function (x, c, p) {
			var W = 1080;
			var H = 1080;
			if (p.photo) {
				cover(x, p.photo, 0, 0, W, 700);
			} else {
				noPhoto(x, W, 700, c.color);
			}
			var g = x.createLinearGradient(0, 700, 0, H);
			g.addColorStop(0, c.color);
			g.addColorStop(1, mix(c.color, '#000000', 0.3));
			x.fillStyle = g;
			x.fillRect(0, 700, W, H - 700);
			if (p.logo) { plate(x, p.logo, 40, 40, 300, 90, false); }
			var t = fit(x, c.title, 960, 3, 240, 68, 34);
			var tc = c.text || contrast(c.color);
			headline(x, t, W / 2, 725, 240, function () { return tc; });
			x.fillStyle = 'rgba(255,255,255,0.22)';
			x.fillRect(50, H - 92, W - 100, 2);
			var bt = c.barText || contrast(c.color);
			text(x, c.dateLong, 50, H - 46, 30, bt, 'left');
			if (c.host) { text(x, c.host, W - 50, H - 46, 32, bt, 'right'); }
		},

		strip: function (x, c, p) {
			var W = 1200;
			var H = 630;
			var S = 535;
			if (p.photo) {
				cover(x, p.photo, 0, 0, W, S);
			} else {
				noPhoto(x, W, S, c.color);
			}
			x.fillStyle = c.head || '#F3F3F5';
			x.fillRect(0, S, W, H - S);
			// Diagonal stripes on the left.
			x.save();
			x.beginPath();
			x.rect(0, S, 150, H - S);
			x.rect(170, S, 90, H - S);
			x.clip();
			x.lineWidth = 4;
			for (var i = -H; i < 300; i += 14) {
				x.strokeStyle = i < 150 ? c.color : 'rgba(0,0,0,0.25)';
				x.beginPath();
				x.moveTo(i, H);
				x.lineTo(i + (H - S), S);
				x.stroke();
			}
			x.restore();
			if (p.logo) {
				var lh = 62;
				var lw = p.logo.width * lh / p.logo.height;
				if (lw > 480) { lw = 480; lh = p.logo.height * lw / p.logo.width; }
				x.drawImage(p.logo, W - 40 - lw, S + (H - S - lh) / 2, lw, lh);
			} else {
				text(x, c.host || c.site, W - 40, S + (H - S) / 2, 44, c.headText || contrast(c.head || '#F3F3F5'), 'right');
			}
		}
	};
	var SIZE = { strip: [1200, 630] };

	function build(c) {
		var fontReady = (d.fonts && d.fonts.load) ? d.fonts.load('64px SolaimanLipi', 'অআকখ').catch(function () {}) : Promise.resolve();
		return Promise.all([fontReady, loadImg(c.img), loadImg(c.logo)]).then(function (r) {
			var style = STYLES[c.style] ? c.style : 'classic';
			var size = SIZE[style] || [1080, 1080];
			var cv = d.createElement('canvas');
			cv.width = size[0];
			cv.height = size[1];
			var x = cv.getContext('2d');
			c.color = c.color || '#B3121C';
			c.foot = c.foot || '';
			STYLES[style](x, c, { photo: r[1], logo: r[2] });
			return new Promise(function (resolve) { cv.toBlob(resolve, 'image/png'); });
		});
	}

	window.nnCardBuild = build;

	/* ------------------------------------------------------------ buttons */
	var bars = d.querySelectorAll('[data-nn-card]');
	if (!bars.length) { return; }

	var canShare = false;
	try {
		var probe = new File([new Blob(['x'])], 'a.png', { type: 'image/png' });
		// Share sheet only on touch devices (phones, tablets); desktop browsers just download.
		var touch = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
		canShare = !!(touch && navigator.canShare && navigator.canShare({ files: [probe] }));
	} catch (e) {}

	Array.prototype.forEach.call(bars, function (bar) {
		var data;
		if (bar.getAttribute('data-bound')) { return; }
		bar.setAttribute('data-bound', '1');
		try { data = JSON.parse(bar.getAttribute('data-nn-card')); } catch (e) { return; }
		var share = bar.querySelector('[data-act="share"]');
		if (share && canShare) { share.hidden = false; }

		bar.addEventListener('click', function (e) {
			var btn = e.target.closest('button[data-act]');
			if (!btn || btn.disabled) { return; }
			var label = btn.textContent;
			btn.disabled = true;
			btn.textContent = data.busy;
			build(Object.assign({}, data)).then(function (blob) {
				if (!blob) { throw new Error('no image'); }
				var file = new File([blob], (data.file || 'card') + '.png', { type: 'image/png' });
				if (btn.getAttribute('data-act') === 'share' && canShare) {
					return navigator.share({ files: [file] });
				}
				var url = URL.createObjectURL(blob);
				var a = d.createElement('a');
				a.href = url;
				a.download = file.name;
				d.body.appendChild(a);
				a.click();
				d.body.removeChild(a);
				setTimeout(function () { URL.revokeObjectURL(url); }, 4000);
			}).catch(function () {}).then(function () {
				btn.disabled = false;
				btn.textContent = label;
			});
		});
	});
}());
