# Changelog

## 1.1.13 (2026-11-06)
- **Fixed:** the Picture style setting is a dropdown with a preview of the chosen style (it showed as an empty text box).

## 1.1.12 (2026-11-06)
- **Added:** four new share-picture styles (Dark, Light, Colour band, Wide strip) next to Classic, with live previews to pick from in Theme Settings → Share picture.

## 1.1.11 (2026-11-05)
- **Fixed:** share picture sent twice to the clipboard: the share sheet is offered only on touch devices and sends the picture alone.

## 1.1.10 (2026-11-05)
- **Changed:** Theme Settings redesigned: tabs (General, Colours and mode, Home page, Articles, Share picture, Footer, Speed and cache), card panels, aligned rows, sticky Save bar.

## 1.1.9 (2026-11-05)
- **Added:** share picture colours are editable: top band, date, main colour, headline, bottom bar and bottom text (Theme Settings → Articles).

## 1.1.8 (2026-11-05)
- **Changed:** the share picture's bottom line now defaults to "বিস্তারিত কমেন্টে" instead of the website address.

## 1.1.7 (2026-11-05)
- **Added:** "ছবি ডাউনলোড" (and "ছবি শেয়ার" on phones) under the article photo: builds a square picture with logo, Bengali date, article photo and headline. Colour and bottom line are set in Theme Settings → Articles.

## 1.1.6 (2026-11-05)
- **Added:** Theme Settings → Home page: choose the category of the top story and of the top-news grid.

## 1.1.5 (2026-11-05)
- **Fixed:** header/footer text colour is chosen for contrast when only a background is picked (white header in dark mode no longer gets pale text); article content can no longer be wider than the screen (old posts' wide images, tables, embeds), which removes the gap on the right on phones.

## 1.1.4 (2026-11-05)
- **Fixed:** footer is fully centred on every screen (logo, name, description, contact, follow, apps, legal links, copyright); the description keeps its line breaks.

## 1.1.3 (2026-11-05)
- **Changed:** footer redesigned: category links in a dotted row; brand, contact and follow + app-download columns; legal links left and copyright right. All parts are set in Theme Settings → Footer.

## 1.1.2 (2026-11-05)
- **Fixed:** photos always fill their 16:9 box (no grey strips); card headlines and summaries are left-aligned site-wide (only the article body is justified); headlines stop at 3 lines and grid rows start level; "আরও পড়ুন" shows 3 stories without summaries; most-read on phones is a swipeable row of photo cards.

## 1.1.1 (2026-11-05)
- **Changed:** X removed from article sharing; mobile article headline smaller; mobile card photos are 16:9 and card headlines left-aligned; most-read uses 16:9 photos; drawer shows the logo (or nothing) instead of the site name; each build raises the version so WordPress detects the update.
- **Added:** Messenger share button (optional Facebook App ID for desktop). Three-column section now shows a photo on the left of each story with clearer separators. Footer is fully centred; most-read rail spacing fixed.
- **Fixed:** dark mode now paints the whole page dark (theme.json no longer forces a white body).
- **Changed:** every story card headline is one size (Theme Settings → Headline size, default 22 px).
- **Added:** separate header and footer logos, footer colours, more social links, and share/social icons in each network's own colour.
- **Fixed:** the article sidebar no longer overlaps "আরও পড়ুন"; it now shows small photos beside latest and most-read headlines.
- **Added:** Theme Settings → Home page sections: pick up to 8 categories in order, or leave a slot on Automatic.
- **Added:** Theme Settings screen in the admin menu (replaces the Customizer). One small option.
- **Added:** automatic light/dark mode by time of day plus a visible switch button (mobile too).
- **Added:** header background/text colours (light and dark) and custom logo support.
- **Added:** scrolling news ticker on the home page.
- **Added:** automatic home sections from the busiest categories.
- **Added:** fragment cache with stale-while-revalidate, cached menus, edge-cache headers, hover prefetch, admin-bar query/time/memory readout.
- **Changed:** much tighter spacing; share icons centred; site name defaults to the WordPress Site Title (no hard-coded name); page language is Bengali.
- **Removed:** duplicate search form and duplicate legal/social block in the drawer; Customizer file.
- **Packaging:** `Update URI: false`, no parent theme, one `narayanganj-news.zip` per release via `build.sh`.

## 1.0.0 (2026-11-05)
First build from the approved v6 design.

- **Added:** a classic PHP theme with theme.json tokens (colour, type, 8pt spacing, shadow). There is no page builder and no framework.
- **Added:** home modules as server-rendered blocks: M03 lead story (A/B/C), M04 top news (4/3/2-up), M05 latest (sidebar/row), M06 most read (1–5/1–10), M07 category block (A–E), M08 lead + rail (main and rail slots) and M09 section heading. Each has a photo and a text-only version, except the text-only designs (M03-C, M07-E). There is also an inline "আরও পড়ুন" block for article bodies.
- **Added:** home patterns. "Home: v6 sample" comes in a photo and a text-only version, plus two lead + rail combos. When the front page is empty, the theme shows the v6 sample order.
- **Added:** the header (M01/M02). It has a masthead, an 8-item nav with an "আরও" overflow, a sticky condensed bar on desktop, a sticky 56/64px bar on tablet and mobile, and a category scroller.
- **Added:** a drawer with search, a sub-category accordion, legal and social links, a focus trap, and Esc/scrim/X close.
- **Added:** search. It sits inline on desktop and opens a full-screen sheet on tablet and mobile. States: empty hint, typing suggestions and submitting.
- **Added:** the article page in the §8 order: share row with copy-link toast, photo or text-only version, source line, "আরও পড়ুন" 3-up, a sticky rail with latest and most read, and back-to-top.
- **Added:** the M10 footer.
- **Added:** settings for the open decisions: site name (placeholder সংবাদপট), date line (off), photos or text-only default, licensed Bold (off), and justified mobile body (on).
- **Added:** a most-read counter. It sends one beacon per session, keeps one post-meta integer, and caches the list for 10 minutes.
- **Performance:** CSS is inlined (about 8 KB gzipped) and one deferred script is about 4 KB gzipped, with no jQuery. The Bengali font is preloaded with `font-display: swap`, and Inter loads only for Latin text. Images use responsive srcset/sizes, native lazy loading and `fetchpriority` on the lead image. New uploads get WebP sub-sizes. Queries use lean WP_Query arguments, prime thumbnails in one query and skip duplicate stories.
- **Editorial rules:** cards and articles never show a category label, date, time or author. Author archives redirect to home. Comments are closed. Video and embed blocks are removed from the editor. All multi-line text is justified with `text-align-last: start` and no hyphenation.
