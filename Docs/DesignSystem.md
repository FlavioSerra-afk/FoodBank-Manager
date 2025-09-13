Docs-Revision: 2025-09-09 (Wave UI/UX Glass — Theme Tab)
# FoodBank Manager — Design System

Defines visual tokens and component specs used across admin and front-end surfaces. Admin screens and public forms share the same token set for parity.

## Tokens
| Token | Default | Notes |
|---|---|---|
| `--fbm-color-text` | `#1d2327` | Body text colour |
| `--fbm-bg` | `#f0f0f1` | Admin background |
| `--fbm-color-surface` | `#ffffff` | Card/background colour |
| `--fbm-color-accent` | `#0B5FFF` | Accent colour (radios/checkboxes use `accent-color`) |
| `--fbm-font-base` | `16px` | Base font size (`14–18`) |
| `--fbm-font-sm` | `calc(var(--fbm-font-base)*0.875)` | Small text |
| `--fbm-font-lg` | `calc(var(--fbm-font-base)*1.125)` | Large text |
| `--fbm-radius` | `20px` | Control/card radius |
| `--fbm-input-height` | `38px` | Base input height |

Radios and checkboxes inherit `--fbm-color-accent` via CSS `accent-color`. Focus outlines use `:focus-visible` and respect high contrast via `@media (forced-colors: active)`.

## Layered glass recipe

- **Top hairline:** `--fbm-inset-top` adds a subtle highlight.
- **Side gradient:** `::after` draws a faint edge gradient.
- **Inset glow:** `--fbm-inset-glow` softens edges and blends layers.

## Theme JSON schema

| Field | Type | Notes |
|---|---|---|
| `version` | int | must be `1` |
| `style` | enum | `glass` or `basic` |
| `preset` | enum | `light`, `dark`, `high_contrast` |
| `accent` | string | `#RRGGBB` colour |
| `glass.alpha` | number | `0–1` (`0.08–0.20` light, `0.18–0.35` dark) |
| `glass.blur` | int | `0–20` px |
| `glass.elev` | int | `0–24` |
| `glass.radius` | int | `6–20` px |
| `glass.border` | int | `1–2` px |

## Theme hook contract

- Body classes: `fbm-theme--{style}`, `fbm-preset--{preset}`, `fbm-rtl` (when RTL).
- Wrappers: `.fbm-admin` for admin pages, `.fbm-public` for front-end blocks.
- Tokens emitted: `--fbm-color-accent`, `--fbm-color-text`, `--fbm-color-surface`, `--fbm-color-border`, `--fbm-shadow-rgb`, `--fbm-glass-alpha`, `--fbm-glass-blur`, `--fbm-card-radius`, `--fbm-border-w`, `--fbm-elev-shadow`, `--fbm-inset-top`, `--fbm-inset-bottom`, `--fbm-inset-glow`.
- Fallbacks: blur is gated by `@supports (backdrop-filter: blur(1px))` ([MDN](https://developer.mozilla.org/docs/Web/CSS/backdrop-filter)). `@media (forced-colors: active)` ([MDN](https://developer.mozilla.org/docs/Web/CSS/@media/forced-colors)), `@media (prefers-reduced-transparency: reduce)` ([MDN](https://developer.mozilla.org/docs/Web/CSS/@media/prefers-reduced-transparency)), and `@media (prefers-reduced-motion: reduce)` ([MDN](https://developer.mozilla.org/docs/Web/CSS/@media/prefers-reduced-motion)) provide accessible fallbacks ([Chrome](https://developer.chrome.com/docs/web-platform/forced-colors/)).

## Menus

- **Admin:** `#adminmenu`, `#adminmenuback`, `#adminmenuwrap`, `#adminmenu .wp-submenu`, `#wpadminbar`, `.ab-item`.
- **Front-end:** `nav .menu`, `.menu-item > a`, `.current-menu-item > a`, `.current-menu-ancestor > a`; theme authors can scope with `.fbm-site-nav`.
- Tokens: `--fbm-color-accent`, `--fbm-glass-alpha`, `--fbm-glass-blur`, `--fbm-card-radius`, `--fbm-border-w`.
- Blur gated by `@supports (backdrop-filter: blur(2px))` ([MDN](https://developer.mozilla.org/docs/Web/CSS/backdrop-filter)).
- Fallbacks: `@media (forced-colors: active)`, `@media (prefers-reduced-transparency: reduce)`, `@media (prefers-reduced-motion: reduce)` (see [MDN](https://developer.mozilla.org/docs/Web/CSS/@media) and [Chrome](https://developer.chrome.com/docs/web-platform/forced-colors/)).

### Focus & Contrast

- `:focus-visible` rings use `color-mix(in oklab, var(--fbm-color-accent) 75%, black 25%)` with `outline-offset:2px` (see [MDN](https://developer.mozilla.org/docs/Web/CSS/:focus-visible)).
- `@media (forced-colors: active)` swaps to system colors and shows `outline:2px solid CanvasText` (see [MDN](https://developer.mozilla.org/docs/Web/CSS/@media/forced-colors)).
- `@media (prefers-reduced-motion: reduce)` removes hover/focus transitions (see [MDN](https://developer.mozilla.org/docs/Web/CSS/@media/prefers-reduced-motion)).
- `@media (prefers-reduced-transparency: reduce)` raises surface alpha and disables blur (see [MDN](https://developer.mozilla.org/docs/Web/CSS/@media/prefers-reduced-transparency)).

## Tables & Forms

- Admin list tables (`.wp-list-table`) use glass rows with sticky headers.
- Public forms (`.fbm-form`) wrap native fields in a glass container with accent focus rings.

### Focus & Contrast examples

```css
.wp-list-table .row-actions a:focus-visible,
.fbm-form input:focus-visible {
  outline:2px solid color-mix(in oklab,var(--fbm-color-accent) 75%, black 25%);
  outline-offset:2px;
}
```

### Fallback matrix

| Feature | Fallback |
| --- | --- |
| `backdrop-filter` | Opaque surface |
| `@media (forced-colors: active)` | System `Canvas`/`CanvasText` |
| `@media (prefers-reduced-motion: reduce)` | Transitions removed |
| `@media (prefers-reduced-transparency: reduce)` | Blur disabled, alpha raised |

## Components
- **KPI Tile** — `.fbm-tile.fbm-card--glass`; icon + label + masked value. Tiles are focusable; border outlines use `--fbm-color-accent`.
- **Card** — `.fbm-card--glass`; translucent surface with `backdrop-filter: blur(var(--fbm-glass-blur))` and soft shadow `var(--fbm-elev)`.
- **Button** — `.fbm-button--glass`; solid text, hover/active/focus use `--fbm-color-accent`.
- **Dashboard tiles** — `.fbm-grid` (3×3, gap 16px). Breakpoints: 2 columns ≤1200px, 1 column ≤720px. Each `.fbm-tile` is focusable (`tabindex="0"`) and uses accent outline on focus.
- **Tabs** — horizontal list; active tab uses accent border-bottom.
- **Table** — responsive, masked PII by default; uses tokenised spacing.
- **Empty State** — card with icon, short text, and primary action.
- **Notice/Toast** — high-contrast background; dismiss button has visible focus ring.

## States & Accessibility
- Focus ring: `color-mix()` blends `--fbm-color-accent` for visibility across presets.
- Hover/active states derive from `--fbm-color-accent`.
- High-Contrast preset swaps glass tokens for solid colours and disables blur.
- `@media (prefers-reduced-transparency: reduce)` disables blur and uses solid surfaces.

## RTL
Spacing and order rely on logical properties; components remain mirrorable when `.fbm-admin` has `direction: rtl`.
