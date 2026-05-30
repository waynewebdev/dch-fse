# Dynamic Custom Homes — FSE

A production-ready WordPress Full Site Editing theme for Dynamic Custom Homes.

- **Slug:** `dch-fse`
- **Requires:** WordPress 6.7+, PHP 8.2+
- **License:** GPL-2.0-or-later
- **No build step.** No npm, no webpack, no Composer. Plain PHP, plain CSS, plain HTML templates.

---

## Content model — two flows that coexist

The theme runs a **hybrid content model**. Decide once, per type of content:

### Pages live in the repo

Marketing pages — Home, About, Services, Areas We Serve, etc. — are authored as PHP files under [`content/pages/`](content/pages/) and synced into `wp_posts` rows by a CLI command. The repo is the source of truth. Edits made in wp-admin are preserved across re-syncs unless the file's `updated_at` is bumped.

### Blog posts live in the database

Posts are written normally in wp-admin. The theme provides single/archive/search templates and an SEO meta box; nothing is file-managed.

Both flows use the same SEO pipeline, the same templates, and the same patterns.

---

## Directory layout

```
dch-fse/
├── style.css                # Theme header — no actual styles
├── theme.json               # FSE config; palette, type, spacing
├── functions.php            # Bootstraps /inc modules
├── index.php                # Fallback (FSE never uses it)
├── README.md                # This file
├── inc/
│   ├── setup.php            # add_theme_support, image sizes, nav locations,
│   │                        #   nav-by-slug filter, pattern category, [dch_year]
│   ├── cleanup.php          # Strips wp_head bloat, emoji, XML-RPC, comments
│   ├── assets.php           # Conditional CSS, preload-swap, critical CSS helper
│   ├── performance.php      # Cache-Control, preconnect, LCP preload helper
│   ├── images.php           # <picture> helper, AVIF/WebP, LCP attributes
│   ├── page-registry.php    # File→DB sync, dch_fse_site(), wp dch sync CLI
│   ├── post-seo.php         # SEO meta box for posts + accessors
│   ├── seo.php              # Unified SEO output (title, meta, OG, Twitter)
│   ├── schema.php           # JSON-LD builders (Org, WebSite, BlogPosting…)
│   ├── sitemap.php          # /sitemap.xml handler + transient cache
│   └── robots.php           # /robots.txt + wp_robots noindex rules
├── parts/
│   ├── header.html          # Skip link, site title, primary nav
│   └── footer.html          # Org info, footer nav, copyright
├── templates/
│   ├── front-page.html      # Home (no post-title, content carries it)
│   ├── page.html            # Generic page (post-title h1 + post-content)
│   ├── single.html          # Post (featured image, title, meta, content,
│   │                        #   "Recent posts" tail)
│   ├── index.html           # Catch-all: header, query loop, footer
│   ├── archive.html         # Archive title + post grid + pagination
│   ├── search.html          # Search query, results or no-results
│   └── 404.html             # Friendly 404 + search
├── patterns/
│   ├── hero.php
│   ├── feature-grid.php
│   ├── cta.php
│   ├── faq.php
│   ├── prose.php
│   ├── image-with-text.php
│   └── stat-row.php
├── content/
│   ├── pages/               # Page registry source files
│   │   ├── home.php
│   │   └── about.php
│   ├── navigation/          # Nav menu source files
│   │   ├── primary.php
│   │   └── footer.php
│   └── site.php             # Site-level config (org, OG defaults, preconnect)
├── assets/
│   ├── css/theme.css        # Reset, base styles, .prose, print
│   ├── js/
│   └── images/              # Drop og-default.jpg, logo.png, AVIF/WebP siblings
└── docs/
    └── design-tokens.md     # Why each color/size/spacing was chosen
```

---

## How to add a new page

Pages are PHP files under `content/pages/`. The file returns an associative array — the schema is the canonical reference.

### Page schema

| Field | Type | Required | Notes |
|---|---|:---:|---|
| `slug` | string | ✅ | URL slug + registry key. Must be unique. |
| `title` | string | ✅ | Page title. Renders as h1 in `page.html`. |
| `blocks` | string \| callable | ✅ | Serialized block markup, or a callable returning one. Callables let you build repeating sections with PHP loops. |
| `updated_at` | string | ✅ | ISO 8601 timestamp (e.g. `2026-05-02T00:00:00Z`). Bump this to trigger re-sync. Compared against `post_modified`; admin edits are preserved unless `updated_at` moves forward. |
| `excerpt` | string | | Used as `post_excerpt` and SEO description fallback. |
| `parent` | string | | Parent page slug. Resolved in a second pass. |
| `order` | int | | `menu_order`. |
| `status` | string | | `publish` or `draft`. Default: `publish`. |
| `is_front_page` | bool | | If true, sets `show_on_front=page` and `page_on_front=<id>`. Only one page in the registry should set this. |
| `seo` | array | | Stored as `_dch_page_seo` (json-encoded). See SEO reference below. |

### `seo` sub-schema

| Field | Type | Notes |
|---|---|---|
| `title` | string | `<title>` text. Falls through to `title — Site Name`. |
| `description` | string | Meta description, OG description, Twitter description. |
| `og_image` | string | Theme-relative path (`/assets/images/foo.jpg`) or absolute URL. |
| `lcp_image` | string | Theme-relative path or URL. Emits a high-priority preload + flags the matching `<img>` for the browser. |
| `robots` | string | `index,follow` (default), `noindex,follow`, or `noindex,nofollow`. |
| `schema` | array | Extra JSON-LD entities, e.g. `['FAQPage' => [['question' => ..., 'answer' => ...], ...]]`. |

### Walkthrough

1. Copy [`content/pages/about.php`](content/pages/about.php) to `content/pages/services.php`.
2. Edit the array: set `slug`, `title`, `updated_at`, write the `blocks`, fill in `seo`.
3. Run `wp dch sync` (see CLI reference below).

The block markup is just a heredoc string of WP block syntax. Patterns (see below) compose well — drop a `<!-- wp:pattern {"slug":"dch-fse/hero"} /-->` reference and the pattern's content gets inlined at render.

If you need PHP logic (loops, conditionals), make `blocks` a callable:

```php
'blocks' => static fn (): string => dch_fse_render_services_grid(),
```

### Adding a navigation menu

Each file in `content/navigation/` returns an array of items. `wp dch sync` converts these into `wp_navigation` posts that the navigation block can reference.

```php
return [
    [ 'label' => 'Home',  'url' => '/' ],
    [ 'label' => 'About', 'url' => '/about' ],
    [ 'label' => 'Services', 'url' => '/services', 'children' => [
        [ 'label' => 'Custom Homes', 'url' => '/services/custom-homes' ],
        [ 'label' => 'Remodeling',   'url' => '/services/remodeling' ],
    ]],
];
```

Templates reference the menu by slug, not by ID:

```html
<!-- wp:navigation {"dchFseNavSlug":"primary"} /-->
```

`inc/setup.php` resolves the slug to the matching `wp_navigation` post ID at render time, so the same template works across environments.

---

## How to write a blog post

Posts are written and edited normally in **Posts → Add New**. Nothing is file-managed.

The theme adds an **SEO** meta box to the post edit screen with four fields:

- **SEO Title** — overrides the `<title>` tag. Defaults to `Post Title | Site Name`.
- **SEO Description** — meta description and OG description. Defaults to the excerpt, or the first paragraph trimmed to ~155 chars.
- **OG Image** — a media-library picker. Defaults to the featured image, then to the site default OG image.
- **Robots** — `index,follow` (default), `noindex,follow`, or `noindex,nofollow`.

The featured image is automatically used as the LCP image: the theme emits a `<link rel="preload" as="image" fetchpriority="high">` for it and adds `fetchpriority="high"` + `decoding="async"` to the `<img>` tag.

---

## SEO output reference

Every page (post, page, archive, etc.) gets a uniform set of meta tags emitted from [`inc/seo.php`](inc/seo.php). The data flows through `dch_fse_seo_context()`, a single resolver that knows where each field comes from.

| Tag | Source priority |
|---|---|
| `<title>` | `seo.title` (page) → `_dch_seo_title` (post) → `Title — Site Name` (default) |
| `<meta name="description">` | `seo.description` → `_dch_seo_description` → excerpt → first paragraph trimmed |
| `<link rel="canonical">` | Always — `get_permalink()` for singular, `get_pagenum_link()` for archives, `home_url('/')` for front page |
| `<meta name="robots">` | Merged via `wp_robots` filter — combines our `index/follow` choice with WP's `max-image-preview:large`. **Single tag.** |
| `<meta property="og:*">` | Type, title, description, url, image (with width/height/alt), site_name, locale |
| `<meta name="twitter:*">` | `summary_large_image` card with title, description, image |
| `<script type="application/ld+json">` | One `@graph` containing Organization (always), WebSite (home), BreadcrumbList (non-home), BlogPosting (single posts), FAQPage (when declared in `seo.schema`) |

**Pagination** (page 2+ of any view) automatically adds `noindex,follow`.

**Search and 404** are always `noindex,follow`.

**OG images** are resolved with `dch_fse_resolve_image_url()` (theme-relative paths get prefixed with `get_template_directory_uri()`); dimensions are read once with `getimagesize()` and cached for a day in a transient.

---

## Pattern reference

All patterns live under [`patterns/`](patterns/) and appear in the block inserter under the **DCH FSE** category.

| Slug | What it is | Notes |
|---|---|---|
| `dch-fse/hero` | Full-bleed cover with headline, subhead, CTA. Includes its own h1. | Use on landing pages where the template doesn't render `post-title` (e.g. `front-page.html`). |
| `dch-fse/feature-grid` | Three-column responsive grid of feature cards. | Use for differentiators, services, or capabilities. |
| `dch-fse/cta` | Centered call-to-action band on a foreground background. | Drop near the bottom of a page to push action. |
| `dch-fse/faq` | Accordion FAQ list using `core/details` for native disclosure. | Zero JS. Pair with `seo.schema => ['FAQPage' => [...]]` to emit FAQPage JSON-LD. |
| `dch-fse/prose` | Long-form text section wrapped in `.prose` utility. | Vertical rhythm + readable measure. Use for narrative content. |
| `dch-fse/image-with-text` | Two-column image + text + CTA. | Add `is-style-reverse` class to flip on desktop. |
| `dch-fse/stat-row` | Three large numbers with labels (years, projects, etc.). | One-line social proof. |

Patterns are auto-discovered by WordPress. To add a new one, create `patterns/your-name.php` with the standard pattern docblock header and block markup. The category is registered in [`inc/setup.php`](inc/setup.php).

---

## WP-CLI command reference

The theme registers one command: `wp dch sync`.

```sh
wp dch sync                 # Sync pages and navigation; idempotent.
wp dch sync --dry-run       # Print what would change. No DB writes.
wp dch sync --prune         # Also delete page posts whose slug isn't in the registry.
                            # Hard guard: never deletes posts (post_type != 'page').
```

Output is bucket-style, e.g.:

```
Syncing pages...
  Pages: created=0 updated=1 skipped=1 pruned=0 errors=0
    updated: home
    skipped: about
Syncing navigation...
  Navigation: created=0 updated=0 skipped=2 pruned=0 errors=0
    skipped: primary, footer
Success: Sync complete.
```

After sync, `do_action('dch_fse_pages_synced', $result)` fires — the sitemap cache busts automatically.

---

## Brand & design notes

The full design system rationale lives in [`docs/design-tokens.md`](docs/design-tokens.md). Quick summary:

- **Palette:** 5 swatches — warm off-white background `#f8f5ef`, warm near-black foreground `#1c1a17`, deep forest accent `#3a5a40`, two warm neutrals (`#a59e92` muted, `#7a7268` border). Defaults to the brand palette only — no custom color picker.
- **Type:** System font stacks. Display = Iowan Old Style → Palatino → Georgia (serif fallback chain). Body = system-ui sans. Zero hotlinking, zero KB over the wire.
- **Type scale:** 7 fluid `clamp()` sizes from 14px to 60px. Body sits at 16–18px.
- **Spacing scale:** 8 steps on a 0.5rem base, from 4px to 80px.
- **Layout:** content 720px, wide 1200px, root padding-aware alignments on.

Tokens are declared in [`theme.json`](theme.json) and consumed everywhere via `var(--wp--preset--…)`. Never hardcode hex/px values outside `theme.json`.

---

## Performance notes

- **Cache-Control** `public, max-age=300, s-maxage=3600` on logged-out HTML responses (filterable via `dch_fse_html_cache_control`).
- **`wp-block-library`** monolithic stylesheet is dequeued site-wide and only re-enqueued on `single`, `archive`, and `search` templates (filterable via `dch_fse_blocks_required_templates`). Modern WP also emits per-block inline styles for blocks actually used.
- **`global-styles`** stays enqueued — it carries the design tokens; dequeueing it would break `var(--wp--preset--…)` references.
- **jQuery** is deregistered on the front end.
- **theme.css** ships with a preload-then-swap pattern (non-blocking) and a `<noscript>` fallback. Currently ~7KB.
- **Critical CSS:** drop a file at `assets/css/critical.css` and it inlines automatically at `wp_head` priority 1.
- **Preconnect:** add origins to `dch_fse_site('preconnect_origins')` in `content/site.php` and they emit as `<link rel="preconnect">`.
- **LCP image:** for posts, the featured image is auto-tagged. For pages, set `seo.lcp_image` in the page registry — the resolved URL is preloaded and the matching `<img>` gets `fetchpriority="high"`. First-match-wins so the same image rendered later (e.g. in a "Recent posts" tail) doesn't get duplicate priority.
- **Images:** every `wp_get_attachment_image()` output gets forced `width`/`height` (CLS prevention) and `loading="lazy"` unless it's the LCP.

---

## Image optimization

The theme reads pre-encoded AVIF and WebP siblings — it does **not** generate them on the fly.

For uploads or theme images you want to serve as modern formats:

1. Encode the AVIF and WebP versions yourself (Squoosh, sharp, ImageMagick, etc.) with the **same base name** as the original.
2. Drop the siblings next to the original (`portfolio.jpg` + `portfolio.webp` + `portfolio.avif`).
3. Use `dch_fse_picture()` in a pattern or template:

```php
echo dch_fse_picture( [
    'src'    => '/wp-content/uploads/2026/05/portfolio-hero.jpg',
    'alt'    => 'Modern Hill Country home with timber accents',
    'width'  => 1600,
    'height' => 1000,
    'sizes'  => '(min-width: 1200px) 1200px, 100vw',
] );
```

If a sibling doesn't exist, the corresponding `<source>` is omitted — the browser falls back to the original `<img>`.

---

## Deployment notes

After deploying:

1. Visit any page once. The version-keyed rewrite-rule self-heal (in [`inc/sitemap.php`](inc/sitemap.php)) flushes if needed so `/sitemap.xml` resolves.
2. Run `wp dch sync` to populate the registry-managed pages and navigation.
3. Sanity-check `/sitemap.xml` and `/robots.txt`.
4. Verify rich results: [Google Rich Results Test](https://search.google.com/test/rich-results), [Schema Markup Validator](https://validator.schema.org/).

To bump rewrite rules later (e.g. add a new endpoint), increment `DCH_FSE_REWRITE_VERSION` in [`inc/sitemap.php`](inc/sitemap.php). The next request will flush exactly once.

---

## Troubleshooting

**`/sitemap.xml` 404s.** Visit any page once to trigger the self-heal flush. Or go to **Settings → Permalinks → Save** to flush manually.

**Navigation block shows the wrong menu.** When `dchFseNavSlug` doesn't resolve to a synced `wp_navigation` post, WordPress falls back to the most recent navigation post. Run `wp dch sync` to create the registry-managed navs.

**Page edits in admin are getting overwritten.** Check the file's `updated_at`. The sync only overwrites when `strtotime(updated_at) > post_modified_gmt`. Admin edits update `post_modified_gmt`, so subsequent syncs skip them — until `updated_at` is bumped.

**`wp dch sync --prune` deleted a page I wanted to keep.** Prune deletes any page post with a `_dch_page_slug` meta value not present in the current registry. If you want to keep a previously-registry-managed page outside the registry, manually clear its `_dch_page_slug` meta first: `wp post meta delete <id> _dch_page_slug`.

**The featured image preload doesn't match the rendered image.** The LCP resolver uses the `large` size by default. If your template uses a different `sizeSlug` on `core/post-featured-image`, override the matching size with the `dch_fse_lcp_image_size` filter:

```php
add_filter( 'dch_fse_lcp_image_size', fn () => 'dch-hero' );
```

**Theme.css isn't loading any token values.** `global-styles` may have been dequeued by another plugin. The theme depends on it for `var(--wp--preset--…)` resolution.

**`<title>` shows the WordPress default.** The theme controls the title via `pre_get_document_title`. If a plugin like Yoast/Rank Math is also installed, it will conflict. The theme is designed to ship without an SEO plugin — remove the plugin or unhook our filter.

---

## Filters reference

Quick index of every filter the theme exposes for downstream extension.

| Filter | Default | Used to |
|---|---|---|
| `dch_fse_site_url` | `home_url('/')` | Override the canonical site URL per-environment |
| `dch_fse_html_cache_control` | `public, max-age=300, s-maxage=3600` | Tune the front-end Cache-Control header (return `false` to skip) |
| `dch_fse_blocks_required_templates` | `['single', 'archive', 'search']` | Templates where `wp-block-library` should be re-enqueued |
| `dch_fse_default_image_args` | (defaults inside `dch_fse_picture`) | Tune per-call defaults for the picture helper |
| `dch_fse_lcp_image_size` | `'large'` | The image size used by the LCP preload (must match the template's `sizeSlug`) |
| `dch_fse_seo_context` | (resolved context) | Mutate the SEO context array right before tags are emitted |
| `dch_fse_schema_graph` | (built `@graph`) | Append/remove JSON-LD entities |
| `dch_fse_sitemap_urls` | (built URL list) | Append/remove URLs in the XML sitemap |
| `dch_fse_robots_txt_lines` | (default lines) | Modify the lines emitted in `/robots.txt` |
| `query_loop_block_query_vars` (with namespace `dch-fse/recent-posts`) | (excludes current post) | Used internally by single.html's tail query |
| `render_block_data` (with `dchFseNavSlug`) | (resolves to `ref`) | Used internally by template parts to reference navs by slug |

---

## License

GPL-2.0-or-later. See `style.css` header.
