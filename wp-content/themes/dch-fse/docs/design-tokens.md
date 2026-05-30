# Design Tokens

This document defines the design system for the **Dynamic Custom Homes - FSE** theme. Every token is declared in `theme.json` and exposed as a CSS custom property under the `--wp--preset--*` namespace.

The tokens here are the source of truth. `theme.css` and any future patterns/templates should consume them via `var(--wp--preset--…)` rather than re-declaring values.

---

## Palette

Brand voice: **warm, residential, premium**. The palette avoids stark blacks/whites in favor of slightly warm neutrals that pair well with timber, stone, and natural-light photography typical of custom-home portfolios. The accent reads "land and timber," not "tech startup."

| Token | Slug | Hex | Use |
|---|---|---|---|
| Background | `background` | `#f8f5ef` | Page background. Warm off-white with a slight cream cast — feels like lime-washed plaster, not stark paper. |
| Foreground | `foreground` | `#1c1a17` | Body text and headings. Warm near-black; less harsh than `#000` and pairs better with the cream background. |
| Accent | `accent` | `#3a5a40` | Links, buttons, focus rings, key emphasis. Deep forest green — evokes timber and landscape, distinct from the terracotta-everywhere builder template look. Reads premium without being trendy. |
| Muted | `muted` | `#a59e92` | Secondary text and most borders. Warm taupe. |
| Border | `border` | `#7a7268` | Emphasis borders, dividers that need to read more strongly than `muted`. Slightly darker neutral. |

**Why this combination:** The palette is intentionally narrow (5 tokens). A custom-home builder's site is image-led — every page will be carrying portfolio photography of natural materials. The chrome around those images should recede. A wide brand palette would compete with the photos.

**Why no defaults:** `defaultPalette: false`, `defaultGradients: false`, `defaultDuotone: false`, and `custom: false` are all set so the editor only ever shows brand-approved swatches.

**Contrast targets:** All foreground/background pairings used on the site should pass WCAG AA. `foreground` on `background` is a strong pass; `accent` on `background` passes AA for normal text. `muted` on `background` is intended for non-essential secondary text only and may not pass AA for body copy — restrict it to bylines, captions, and labels.

---

## Typography

### Pairing

- **Display** (`var(--wp--preset--font-family--display)`) — high-quality serif fallback chain: `"Iowan Old Style", "Palatino Linotype", "URW Palladio L", P052, "Source Serif Pro", Georgia, serif`. macOS/iOS resolves to Iowan Old Style; Windows to Palatino Linotype; Linux to URW Palladio or P052; final fallback is Georgia. All are well-drawn book serifs.
- **Body** (`var(--wp--preset--font-family--body)`) — system UI sans stack: `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`. Matches the host OS, ships zero bytes over the wire, has zero render-blocking risk.

**Why system stacks:** Self-hosted woff2 would add 30–80KB plus a render-blocking dependency, and the brand has no current custom typeface. System stacks deliver instant first paint and respect user-installed font preferences. If a custom typeface is adopted later, drop the woff2 files into `/assets/fonts/` and replace the `fontFamily` declarations with `fontFace` blocks in `theme.json` — no other code changes required.

### Type scale

Fluid `clamp()` values; min targets the smallest mobile viewport, max targets desktop ≥1440px. Body sits at ~16px on mobile, ~18px on desktop.

| Token | Slug | Min → Max | Used for |
|---|---|---|---|
| Small | `sm` | 14px → 15px | Captions, fine print |
| Base | `base` | 16px → 18px | Body copy |
| Medium | `md` | 18px → 20px | Lead paragraphs |
| Large | `lg` | 20px → 24px | h4 |
| X-Large | `xl` | 24px → 30px | h3 |
| 2X-Large | `2xl` | 30px → 40px | h2 |
| 3X-Large | `3xl` | 40px → 60px | h1 (hero) |

**Why a 7-step scale:** Enough for a clear typographic hierarchy (h1–h4 + body + lead + caption) without enabling random in-between sizes. `customFontSize: false` blocks the editor from offering arbitrary values, so everything on the site stays on-scale.

**Why hand-rolled clamps instead of WP's `fluid: true`:** Hand-tuned clamps give precise control over the breakpoints and growth rate. WP's fluid mode is a reasonable default but it interpolates uniformly across all sizes — large headings end up overshooting on wide displays.

### Element defaults

`styles.elements` in `theme.json` sets defaults so the editor and the front end render on-brand without per-block overrides:

- `heading` — display serif, weight 600, tight tracking, color = foreground.
- `h1` — 3xl, weight 700, ultra-tight (-0.02em), line-height 1.05.
- `h2` — 2xl, weight 600.
- `h3` — xl, weight 600.
- `h4` — lg, weight 600.
- `h5` / `h6` — body sans, uppercase eyebrow style.
- `link` — accent color, underlined, hover/focus → foreground.
- `button` — accent fill on background text, square corners, hover/focus invert to foreground fill.

---

## Spacing

Base unit: `0.5rem` (8px). 8-step scale from 4px to 80px. Doubled steps from `md` upward give clean rhythm without pixel-pushing.

| Token | Slug | Value | Px (root 16) |
|---|---|---|---|
| 2X-Small | `2xs` | 0.25rem | 4px |
| X-Small | `xs` | 0.5rem | 8px |
| Small | `sm` | 0.75rem | 12px |
| Medium | `md` | 1rem | 16px |
| Large | `lg` | 1.5rem | 24px |
| X-Large | `xl` | 2rem | 32px |
| 2X-Large | `2xl` | 3rem | 48px |
| 3X-Large | `3xl` | 5rem | 80px |

**Why these stops:** 4 → 8 → 12 → 16 covers all in-component spacing (button padding, gaps inside cards). 24 → 32 covers component-to-component spacing. 48 → 80 covers section-to-section vertical rhythm. The non-power-of-two midpoints (12, 24, 48) prevent the scale from feeling rigid.

`defaultSpacingSizes: false` removes WP's bundled scale so the picker only ever shows brand sizes.

---

## Layout

| Token | Value | Use |
|---|---|---|
| `contentSize` | `720px` | Default content column. Comfortable measure for long-form prose at base font size. |
| `wideSize` | `1200px` | Wide alignment cap for grids, hero images, and full-bleed-with-margin sections. |

`useRootPaddingAwareAlignments: true` means `align="full"` blocks bleed to viewport edges while contained blocks honor the root horizontal padding (`md` / 16px on each side, set in `styles.spacing.padding`). This is the modern FSE pattern and it eliminates the awkward double-gutter behavior of older themes.

---

## Other settings

- `appearanceTools: true` — turns on the modern editor controls (border, link color, padding, margin, etc.) without needing each one toggled individually.
- `color.custom: false` / `color.customGradient: false` — locks the color picker to the brand palette.
- `typography.customFontSize: false` — locks font size to the scale.
- `typography.fluid: false` — fluid scaling is handled per-size via the hand-rolled clamps; WP's auto-fluidization is off.
- `typography.dropCap: false` — disabled at the source; we won't need it and it reduces editor noise.

---

## Adding to the system

Before adding a new token, check if an existing one fits. If a new token is genuinely needed:

1. Add it to `theme.json` under the appropriate `settings.*` array.
2. Document it here with a short rationale ("why this and not the existing X").
3. Reference it via `var(--wp--preset--…)` in `theme.css` and patterns — never hardcode the hex/px/rem value outside `theme.json`.

The single source of truth is `theme.json`. This document explains the choices; it does not replace them.
