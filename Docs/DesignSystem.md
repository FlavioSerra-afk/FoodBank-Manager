Docs-Revision: 2025-09-09 (Wave UI/UX Glass + Dashboard First)
# FoodBank Manager — Design System

Defines visual tokens and component specs used across admin and front-end surfaces.

## Tokens
- `--fbm-color-accent` — action/hover blue.
- `--fbm-color-surface` — base card background.
- `--fbm-color-text` — default text colour.
- `--fbm-color-border` — low-contrast border colour.
- `--fbm-glass-bg` — translucent card background.
- `--fbm-glass-border` — border colour on glass surfaces.
- `--fbm-glass-blur` — backdrop blur radius.
- `--fbm-card-radius` — border radius for cards and tiles.
- `--fbm-elev` — box-shadow for raised surfaces.

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
