Docs-Revision: 2025-09-09 (Wave UI/UX Glass — Theme Tab)
# FoodBank Manager — Design System

Defines visual tokens and component specs used across admin and front-end surfaces.

## Tokens
| Token | Default | Range |
|---|---|---|
| `--fbm-color-accent` | `var(--fbm-accent, #3B82F6)` | Accent colour |
| `--fbm-shadow-rgb` | `0 0 0` | Base drop shadow colour |
| `--fbm-glass-alpha` | `var(--fbm-alpha, 0.22)` | Surface alpha |
| `--fbm-glass-blur` | `var(--fbm-blur, 14px)` | Blur radius |
| `--fbm-card-radius` | `var(--fbm-radius, 20px)` | Border radius |
| `--fbm-border-w` | `var(--fbm-border, 1px)` | Border width |
| `--fbm-elev-shadow` | `0 8px 32px rgba(var(--fbm-shadow-rgb)/0.10)` | Elevation shadow |
| `--fbm-inset-top` | `inset 0 1px 0 rgba(255 255 255 / 0.50)` | Top hairline |
| `--fbm-inset-bottom` | `inset 0 -1px 0 rgba(255 255 255 / 0.10)` | Bottom hairline |
| `--fbm-inset-glow` | `inset 0 0 20px 10px rgba(255 255 255 / 0.60)` | Inner glow |
| `--fbm-color-surface` | preset | n/a |
| `--fbm-color-text` | preset | n/a |
| `--fbm-color-border` | preset | n/a |

Focus and hover states derive from `--fbm-color-accent`; borders and outlines use the blue accent to ensure visibility.

## Layered glass recipe

- **Top hairline:** `--fbm-inset-top` adds a subtle highlight.
- **Side gradient:** `--fbm-card--glass::after` uses `--fbm-inset-bottom` for light refraction.
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
- Fallbacks: blur is gated by `@supports (backdrop-filter: blur(1px))` ([MDN](https://developer.mozilla.org/docs/Web/CSS/backdrop-filter)). `@media (forced-colors: active)` ([MDN](https://developer.mozilla.org/docs/Web/CSS/@media/forced-colors)) and `@media (prefers-reduced-transparency: reduce)` ([MDN](https://developer.mozilla.org/docs/Web/CSS/@media/prefers-reduced-transparency)) provide accessible fallbacks ([Chrome](https://developer.chrome.com/docs/web-platform/forced-colors/)).

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
- Focus ring: `outline: var(--fbm-focus, 0 0 0 2px var(--fbm-color-accent));`.
- Hover/active states derive from `--fbm-color-accent`.
- High-Contrast preset swaps glass tokens for solid colours and disables blur.
- `@media (prefers-reduced-transparency: reduce)` disables blur and uses solid surfaces.

## RTL
Spacing and order rely on logical properties; components remain mirrorable when `.fbm-admin` has `direction: rtl`.
