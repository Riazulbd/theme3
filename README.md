# Narayanganj News: WordPress theme (v1.0.0)

A light, text-first Bengali news theme built from the approved v6 design. It shows no video, no ads, and no category, date or author on any story. Text is SolaimanLipi Regular, justified on a strict 12/8/4 column grid.

**Why a classic theme (PHP templates + theme.json) and not a full block theme?**
The header, drawer, search and article layout are fixed by the design and need exact markup and behaviour, which PHP templates control best. The home page has to be editor-managed, so it is a normal WordPress page built from module blocks. Editors add, remove, reorder and fill modules in the block editor. theme.json still supplies the design tokens to the editor.

Requirements: WordPress 6.6+ and PHP 7.4+. No plugins are required.

---

## Install

1. Copy this folder to `wp-content/themes/narayanganj-news/`. A short folder name without spaces is easiest. Or upload the ready-made `narayanganj-news.zip` in **Appearance → Themes → Add New → Upload**. Every release is one zip with the same folder name (`narayanganj-news`), so uploading a new one replaces the old one and never installs a second theme. The theme has no parent theme and sets `Update URI: false`, so WordPress.org can never offer a different theme as its "update".
2. Go to **Appearance → Themes** and activate **Narayanganj News**.
3. Go to **Settings → Permalinks** and save once. This refreshes the rewrite rules and REST routes.
4. Open **Theme Settings** in the admin menu (left sidebar) and review the settings (see below). Home sections are picked automatically from your busiest categories; nothing to map by hand.
5. Set up menus in **Appearance → Menus**:
   | Location | Holds | If empty |
   |---|---|---|
   | Primary nav | Sections for the desktop bar and the mobile scroller. The first 8 items show; the rest go under "আরও". | The busiest top-level categories |
   | Side drawer | The full section list. **Sub-items become the accordion**, e.g. বাংলাদেশ → জাতীয়, রাজনীতি, অপরাধ, রাজধানী. | The primary nav, then categories with their child categories |
   | Footer category links | Footer columns (4 on desktop and tablet, 2 on mobile) | The primary nav |
   | Legal links | আমাদের সম্পর্কে, যোগাযোগ, গোপনীয়তা নীতি, ব্যবহারের শর্তাবলি | The privacy policy page only |
6. Build the home page:
   1. Create a page called "প্রচ্ছদ".
   2. In the editor, open the pattern inserter → **সংবাদপট: প্রচ্ছদ** → **Home: v6 sample**. A text-only version is also there.
   3. Publish the page.
   4. In **Settings → Reading**, choose "A static page" and pick it as the homepage.
   5. Optional: also pick a "Posts page" there. The "আরও দেখুন" link on দিনের সর্বশেষ points to it.

   Until a static front page exists (or while it is empty), the home page shows the same v6 sample order automatically.
7. For each story:
   - **Excerpt** is the summary (deck).
   - **Featured image** is the photo. Its caption is shown under the photo.
   - The **Article display** panel in the editor sidebar sets the photo or text-only version and the source line ("সূত্র: …").

## Open decisions (settings, not hard-coded)
These are on the **Theme Settings** screen in the admin menu (one small option, `nn_settings`; old Customizer values are copied over once).

| Decision | Setting | Default |
|---|---|---|
| Site name (placeholder) | Wordmark / site name | সংবাদপট (empty = Site Title) |
| Date line in the masthead | Show today's date line | Off |
| Photos or text-only | Default story display | Photos. Each module and each article can override it. |
| Licensed Bold font | Use a licensed SolaimanLipi Bold | Off. It only works when `assets/fonts/SolaimanLipi-Bold.woff2` exists. |
| Justified article body on mobile | Justify the article body on mobile | On |

The same section also holds the footer about text, social URLs (each icon hides when its URL is empty), the most-read counter on/off, the most-read look-back days, and grey placeholders for stories without a photo.

## Home modules (block inserter → "সংবাদপট মডিউল")
| ID | Block | Layouts | Photo / text-only |
|---|---|---|---|
| M03 | লিড খবর · Lead story | A image lead + list · B full-width image · C text-only | A, B: both · C: text |
| M04 | শীর্ষ খবর · Top news grid | 4-up · 3-up · 2-up | both |
| M05 | দিনের সর্বশেষ · Latest | sidebar (4–12 headlines; 6 on mobile) · row (4); optional "আরও দেখুন" | headline-only, no timestamps |
| M06 | সর্বাধিক পঠিত · Most read | 1–5 · 1–10 (Bengali digits) | headline-only |
| M07 | বিভাগ ব্লক · Category block | A 1 large + 4 list · B 4-up · C 1 large + 2 medium + 3 list · D 2 sections · E 3 sections as headline columns | A–D: both · E: text |
| M08 | মূল + রেল · Lead + rail | 8-col main slot + 4-col rail (desktop); stacks on tablet and mobile | any module inside |
| M09 | বিভাগ শিরোনাম · Section heading | title only · title + "আরও দেখুন" link | n/a |
| — | আরও পড়ুন · Inline related | One story link inside an article body | n/a |

M01 header, M02 nav and M10 footer are part of the theme templates.

Every module has these options:
- **Photos:** site default, on or off.
- **Stories from:** newest, one category, or hand-picked. Hand-picked stories come first and the rest fills automatically.
- **Skip newest N.**
- **Skip stories already on the page:** on by default, so the same story is not repeated down the page.

Category blocks with no section chosen pick the busiest section not yet used on the page. A module with no stories outputs nothing, so the page never shows an empty gap.

## Files
```
style.css            theme header only
functions.php        loads /inc
theme.json           design tokens for the editor
header.php           M01 masthead + M02 nav + sticky condensed bar
footer.php           M10 footer + drawer + copy-link toast
front-page.php       home: static page blocks, or the v6 sample order
single.php           article page (design §8)
page.php, index.php, 404.php, searchform.php, woocommerce.php
template-parts/header/search.php    inline search / mobile search sheet
template-parts/header/drawer.php    off-canvas drawer with accordion
template-parts/article/share.php    share row
inc/helpers.php      settings, Bengali digits/date, icons, wordmark, images
inc/setup.php        supports, menus, image sizes, excerpts, body classes
inc/customizer.php   open-decision settings
inc/assets.php       inline CSS, font preload, deferred JS, editor script
inc/cleanup.php      performance trims; no author archives, comments or video blocks
inc/meta.php         per-article photo/text-only + source line
inc/menus.php        menu trees with category fallback
inc/cards.php        story card atom (XL, L, M, S, headline, numbered)
inc/modules.php      M03–M09 renderers + lean queries
inc/blocks.php       block registration
inc/views.php        most-read counter (REST beacon + cached list)
inc/patterns.php     home patterns
assets/css/main.css  all front-end styles (inlined in <head>)
assets/css/editor.css
assets/js/theme.js   drawer, search, overflow, sticky header, share, rail, beacon
assets/js/editor.js  module blocks UI, article panel, removes video/embed blocks
assets/fonts/        SolaimanLipi (OFL), Inter Latin subset (OFL), FONT-LICENSES.md
```

## Performance notes
- **CSS:** no render-blocking stylesheet. `main.css` is minified and inlined in `<head>` (about 6–8 KB gzipped). Use `add_filter( 'nn_inline_css', '__return_false' )` to load it as a file instead.
- **JS:** one script, about 3.5 KB gzipped, loaded with `defer`. No jQuery.
- **Fonts:**
  - SolaimanLipi WOFF2 (81 KB) is preloaded and uses `font-display: swap`.
  - Inter (16 KB) downloads only on pages that contain Latin letters.
- **Images:**
  - Featured images are WordPress responsive images with srcset and slot-accurate `sizes`.
  - Images are lazy-loaded natively. The lead image is eager and uses `fetchpriority="high"`.
  - Every image box has a fixed aspect ratio, so there is no CLS.
  - New uploads get WebP sub-sizes when the server supports it.
- **Queries:**
  - Each module runs one lean `WP_Query`: `no_found_rows`, no term cache unless permalinks use `%category%`, and thumbnails primed in a single query.
  - The most-read list is cached for 10 minutes.
  - Menus and categories use WordPress object caching.
- **Cache-friendly:** pages hold no per-user output, so they are safe for a full-page cache or Cloudflare. View counts come from a client beacon, so they still work behind the cache. The optional date line is part of the cached HTML; purge daily if it is turned on.

## Hooks and customisation points
### Filters
| Filter | What it changes |
|---|---|
| `nn_defaults` | Default values of every Customizer setting |
| `nn_option_{key}` | One setting's value (e.g. `nn_option_nn_image_mode`) |
| `nn_brand_name` | Site name used in the wordmark, footer and copyright |
| `nn_images_on` | Whether photos show (args: bool, mode) |
| `nn_date_line` | Masthead date string |
| `nn_icon_paths` | SVG paths of the icon set |
| `nn_image_size`, `nn_image_attributes` | Image size and attributes used by cards |
| `nn_card_options`, `nn_card_html` | Story card options and markup |
| `nn_card_summary`, `nn_excerpt_words` | Card summary text and length (default 28 words) |
| `nn_heading_html` | M09 section heading markup |
| `nn_module_query_args`, `nn_module_posts` | WP_Query args and results of every module |
| `nn_popular_posts`, `nn_most_read_cache_ttl` | Most-read results and cache time (default 600 s) |
| `nn_latest_url` | "আরও দেখুন" target of M05 |
| `nn_related_posts` | "আরও পড়ুন" stories on the article page |
| `nn_article_latest_args`, `nn_article_most_read_args` | Article rail modules |
| `nn_nav_items`, `nn_primary_nav_limit`, `nn_fallback_nav_count` | Menu trees, desktop nav length (8), fallback count (12) |
| `nn_social_links`, `nn_share_links` | Footer/drawer social links; article share targets |
| `nn_search_form` | Search form markup |
| `nn_copyright` | Footer copyright line |
| `nn_home_markup` | Block markup of the default/sample home page |
| `nn_block_defs` | Module block definitions (attributes, layouts, labels) |
| `nn_disallowed_blocks` | Blocks removed from the editor (default `core/video`, `core/embed`) |
| `nn_script_data` | Data passed to `theme.js` |
| `nn_inline_css` | `false` loads `main.css` as a file |
| `nn_convert_uploads_to_webp` | `false` keeps JPEG/PNG sub-sizes |
| `nn_disable_comments`, `nn_disable_author_archives` | `false` brings comments / author archives back |

### Template overrides (child theme)
Copy any of these files into the child theme to override it:
- `header.php`, `footer.php`, `front-page.php`, `single.php`, `index.php`, `page.php`
- `template-parts/header/search.php`, `template-parts/header/drawer.php`, `template-parts/article/share.php`

The parent CSS loads first; the child `style.css` is enqueued after it automatically. A child theme can also supply `assets/fonts/SolaimanLipi-Bold.woff2`.

### CSS custom properties
Override these on `:root` in a child theme:
- Colours: `--ink-900/700/500`, `--line`, `--surface`, `--paper`, `--purple`, `--cyan`, `--ph`
- Grid: `--margin`, `--gutter`, `--maxw`, `--rhythm`
- Type: every `--fs-*` / `--lh-*` pair

## Accessibility
- There is a skip link. Landmarks label the header, nav, drawer, rail and footer.
- Focus ring: 2px purple, offset 2.
- Tap targets are at least 44×44.
- The drawer has a focus trap and closes with Esc, the scrim or X; focus returns to the button that opened it.
- The accordion and "আরও" buttons use `aria-expanded`. The active section has `aria-current="page"`.
- The copy-link toast is a `role="status"` live region.
- Cyan is never used for text.
- `prefers-reduced-motion` is respected.

## Known trade-offs
- Justified Bengali text on narrow screens can show wide word gaps. This was a Director decision for the article body; it can be switched off for mobile in the Customizer.
- Section, search-results and 404 pages were outside the v6 design scope. They reuse the module look.


## 1.1.0 additions
- **Settings screen** in the admin menu: general, light/dark, header colours, ticker, articles, speed, social. No Customizer.
- **Light/dark mode** by the visitor's local time (default 6:00–18:00 light), a bordered switch button in the header (also on mobile). A manual choice lasts until the next automatic switch.
- **Header colours** (light and dark) for logos without a transparent background. Upload a logo in Site Identity.
- **News ticker** on the home page: continuous scroll, pauses on hover, focus and with a button, static when motion is reduced.
- **Automatic sections**: the busiest top-level categories, cached and refreshed as content grows.
- **Speed**: fragment cache with stale-while-revalidate (one rebuild, no stampede), cached menus, edge-cache headers (`s-maxage`, `stale-while-revalidate`, Early Hints `Link`), link prefetch on hover, no global-styles dump, meta cache loaded only when photos show.
- **Admin bar** shows queries · ms · MB on the live site (admins only). Use it to check the targets; they were not measured in a real WordPress here.
- **Build**: run `./build.sh` for `dist/narayanganj-news.zip`.
