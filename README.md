# Avallone — WordPress Theme

Custom WordPress theme for **Avallone** (avallone.ee) — a premium wine and spirits retailer in
Estonia, established 1994.

Designed and developed by **MarketingSharks**.

- Development site: https://sharks.pw/avallone-veeb/
- Requires: WordPress 6.5+, PHP 8.1+, Advanced Custom Fields PRO, WooCommerce

---

## Approach

A fully custom classic PHP theme with modern block-editor support. No page builder, no Elementor,
no CSS framework, no build step.

- PHP templates and reusable template parts
- ACF PRO for editable page components
- Vanilla CSS, organised in layers, driven by design tokens
- Minimal vanilla JavaScript, only where CSS cannot do the job
- WooCommerce planned in from the foundation up

The Avallone CVI is the canonical design source. Any value it defines exists once as a token in
`assets/css/settings/tokens.css` rather than being repeated across component CSS.

---

## Structure

```
avallone/
├── style.css              Theme header only — not enqueued, contains no rules
├── functions.php          Constants + requires. Behaviour lives in /inc
├── index.php              Universal fallback template
├── header.php             Document skeleton (no header design yet)
├── footer.php             Document close (no footer design yet)
├── theme.json             Block editor presets, referencing the CSS tokens
│
├── assets/css/
│   ├── settings/tokens.css        All design tokens — single source of truth
│   ├── base/                      reset, typography, global
│   ├── layout/layout.css          .container, .grid-12
│   ├── components/                buttons, forms
│   └── woocommerce/               Conditional WooCommerce compatibility layer
│
├── inc/
│   ├── setup.php          Theme supports, editor styles
│   ├── enqueue.php        Fonts, CSS layer chain, resource hints
│   ├── acf.php            ACF JSON save/load points
│   └── woocommerce.php    WooCommerce support, content wrappers, conditional CSS
│
└── acf-json/              ACF field group definitions (version controlled)
```

### CSS layers

There is no build step. Each stylesheet is registered as its own handle with the previous one as its
dependency, so WordPress enforces the cascade:

```
fonts → tokens → reset → typography → global → layout → buttons → forms
                                                            └→ woocommerce (shop pages only)
```

The list lives in one place — `avallone_style_layers()` in `inc/enqueue.php` — and is reused for
`add_editor_style()`, so the editor and the front end cannot drift apart. Each file is versioned by
its modification time, giving per-file cache busting for free.

**Adding a component stylesheet:** create the file under `assets/css/components/`, then append it to
`avallone_style_layers()`. Nothing else needs to change.

### Design tokens

`assets/css/settings/tokens.css` is the only file permitted to define a CSS custom property. Every
token carries a comment pointing at the CVI section it comes from.

The one unavoidable exception is the select-chevron stroke colour in `components/forms.css`: SVG data
URIs are parsed outside the CSS cascade and cannot reference a custom property. It is flagged in
place.

Breakpoints (375 / 768 / 1024 / 1280 / 1440) are written as literals in `@media` rules, since custom
properties are not valid in media query conditions and the project has no build step. The scale is
documented at the top of `tokens.css`.

### Typography

Four families, loaded from Google Fonts via `wp_enqueue_style()` (never `@import`):

| Token | Family | Role |
|---|---|---|
| `--font-display` | Playfair Display | H1 hero, editorial pull quotes |
| `--font-heading` | Libre Caslon Text | H2–H4, editorial body |
| `--font-ui` | Work Sans | Body, navigation, buttons, labels, H5/H6 |
| `--font-meta` | Inter | Utility nav, footer, small text, metadata, legal |

`<body>` inherits `--font-body`, an alias currently pointing at Work Sans. The CVI is internally
inconsistent here — §3.1's role table assigns body text to Inter, while §3.2's `.type-body` class and
every component spec (forms, buttons, cards, nav) use Work Sans. The alias exists so the decision can
be reversed in one line.

Type sizes are deliberately not tokenised into a numeric scale. The CVI expresses typography as
complete named styles, so each size, line-height and tracking is declared once inside its `.type-*`
class in `base/typography.css`.

### ACF

`inc/acf.php` pins field group JSON to `acf-json/` in the **template** directory, so definitions stay
with the components that render them even if a child theme is ever added. No field groups exist yet.

Components will follow one directory per component, each self-contained:

```
template-parts/components/hero/hero.php  +  assets/css/components/hero.css
```

### WooCommerce

The theme declares WooCommerce support and replaces WooCommerce's content wrappers with its own
`.container`, so shop pages sit inside the theme layout **without overriding a single WooCommerce
template**. The wrapper deliberately does not open a `<main>` — `header.php` already provides one.

`assets/css/woocommerce/woocommerce.css` loads only on shop pages and is registered as depending on
WooCommerce's own stylesheets, so it lands last in the cascade by dependency order. That is why it
contains no `!important`.

> **Note:** this installation uses the **block-based** Cart and Checkout (`wp:woocommerce/cart`).
> Those render as `.wc-block-components-*` React components and cannot be themed by overriding
> classic PHP templates. They will need their own approach when that phase is designed.

---

## Current status

**Phase 1 — foundation and design system. Complete.**

The site is intentionally not visually complete. This phase delivers the technical base so that
components can be added from Figma one at a time.

Not yet built: page sections, inner page layouts, header, footer, navigation, menus, product grid,
product card, single product, cart, checkout, account pages, mini cart, age gate, language switcher,
the icon library, and any ACF field group.

---

## Conventions

- WordPress coding standards; all functions prefixed `avallone_`
- All front-end output escaped; all strings translatable with the `avallone` text domain
- No inline CSS in templates, no inline JS without a strong reason
- No `!important`
- No hardcoded design values where a token exists
- Components own their CSS and depend on no other component

---

## Accessibility

The CVI is followed except where it would produce an inaccessible result. Two deviations are recorded
in the code:

1. **Form focus** — §12 specifies `outline: none`. The outline is set transparent instead, so Windows
   High Contrast Mode (which discards box-shadows) still shows a focus indicator. Appearance is
   unchanged.
2. **Placeholder contrast** — §12's placeholder colour measures 2.03:1 on cream, below WCAG AA. The
   CVI value is kept, and the accessible name is carried by the visible `<label>` (16.3:1), which §12
   already mandates. A placeholder must never be a field's only cue.

Baseline: semantic HTML, a skip link, keyboard-visible focus on every interactive element, and
`prefers-reduced-motion` respected.
