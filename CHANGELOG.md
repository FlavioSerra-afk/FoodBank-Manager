## [2.1.3] — 2025-09-13
### Added
- Typography settings (H1–H6, body, small, link/muted colors) with live preview and FBM-scoped selectors.
- Tabs design tokens (size, padding, radius, gap, indicator, states) wired to ARIA-compliant tab markup.
### Fixed
- Variables emitted only under `.fbm-scope` in `@layer fbm`; no bleed into wp-admin chrome.
### A11y
- :focus-visible outlines maintained for tabs; radios/checkboxes keep accent-color.

## [1.8.0](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.8.0...1.8.0) (2025-09-13)


### Features

* add jobs admin UI and mail replay ([8c967fd](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8c967fd8a1f7aecb0f0d55a683ec22408be24217))
* add jobs admin UI and mail replay ([bd0ae6a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bd0ae6a446d569c35b97f2a3c38f82a558eec7e4))
* add jobs capability and version bump ([359d9c8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/359d9c89ff5c97929ac0533a5c01c4740167fb45))
* diagnostics mail test UI and telemetry polish ([948fc48](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/948fc4852fd5e5ae362327c2f4e435bfb4ff3876))
* **diagnostics:** mail test UI, rate-limit headers, and privacy preview ([631d320](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/631d3209995ca01a7a112caf2a62dde3a2c983cd))
* **jobs:** add manage jobs capability ([34e2022](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/34e2022bad0b6cb198f20ba13e7159fcb48e506b))
* **jobs:** enforce capability checks and add caps doctor ([f606029](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f60602901b50dbcffed0b454e3400b0dff88150c))
* **jobs:** enforce capability checks and add caps doctor ([d39e617](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d39e6174da237ce6a1369fe1349379d327bafbb1))
* **security:** per-role scan throttle and admin settings ([af558d3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/af558d3e4d42f5095a579be98ffa3f7b3d269f6a))
* **security:** per-role scan throttles and admin settings ([c9f495e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c9f495e947318cbaef9a9088e19c65d2c6c184be))
* **theme:** add live preview and scoped tokens ([b8d9c4e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b8d9c4ebdd8de6b9962fedf1082ad06f1b5228e6))
* **theme:** add typography and tabs tokens ([688bcb9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/688bcb98374a2d14f0d24061e8d1702c61f3a09c))
* **theme:** map ui components to tokens and svg icons ([6b73852](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6b7385220a14057113ff4f4ff92bc308e42cbeb0))
* **theme:** scope menu tokens and design controls ([0d85f53](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0d85f53fb4fb3ae4c62b87f6b556d38ae9612b70))
* **theme:** token-driven components and icon system ([6b90e6d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6b90e6d6ad1cb079d439b3cdb9db604091b8bac2))


### Bug Fixes

* **admin:** scope theming and restore dashboard grid ([7b369a8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7b369a85611bd46b1077411eae1cd68b39848dfd))
* **core:** finalize v1.11.3 with quiet i18n and header parity ([f7b96fc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f7b96fc564bc4450d9491dafd749c522eee7fda2))
* production autoload and FBM admin theming ([6891131](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6891131ac13fa9ad7b2b41cf53fba1cebe33f751))
* **rest:** enforce arg validation and nonce checks ([05233c1](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/05233c1a67c8087d8b740b6e1621c20bdd00f674))
* **rest:** normalize error contract ([060d39b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/060d39ba1d63d0f79a2ea22b8139053a7be737a7))
* **rest:** normalize error contract ([9b567cb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9b567cb2e61a4d6705074341e91cab0818dd0ae3))
* scope theming and restore dashboard grid ([95403ba](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/95403ba594cfaa150c2af2ccb77b3681b6f4d9c8))
* **theme:** apply admin theme across FBM pages ([78317ce](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/78317cec906f7aa7b0cfb504dfe9dbb7283997fd))
* **theme:** isolate plugin styling scope ([56b2272](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/56b2272fb41f93c8e1dd4b79fb1863465065de71))
* **theme:** isolate plugin styling scope ([a09da6e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/a09da6eed82dbd8ca034a86edc0bdcd52611b3a1))
* **theme:** register fbm_theme option with color picker ([7c8880c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7c8880cee15d28ce49e94c907862dd33e1339995))
* **theme:** register option and add color picker ([f93712b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f93712b61efb420dbbb608111c04ab622db27d80))
* **theme:** register settings API and tokenized variables ([8fb5df3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8fb5df3be4b6b217c761d328c4217f793aeb2b50))
* **uninstall:** multisite cleanup and csv export hardening ([d8c811c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d8c811cf9ba969b780cbfba9b51a00798c9a2ada))


### Miscellaneous Chores

* align release-please to 1.8.0 ([d2f62b3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d2f62b35a185ad2b07fb7394da20d59e24ef64fc))
* bump readme version ([8dd44a5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8dd44a5e50c442dafdc7b3fe30ae076efa566eb2))
* bump readme version ([44b0d86](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/44b0d8646df1ad1d3c7944df78501c5025d9b5e9))
* finalize 1.11.2 release ([38a53ae](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/38a53ae62a37039303c2b198c0bfa74926f0815c))
* harden throttling and diagnostics ([f3d135d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f3d135d80d6733a2bce0ff966c8d4526f2ff8568))
* **main:** release 1.8.0 ([07e3b4c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/07e3b4c68e614f933fcc0b49619738b30a1f8306))
* **main:** release 1.8.0 ([9c887af](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9c887af187e516de451f7a6e58cce19633c8bd1f))
* **main:** release 1.8.0 ([be89bfe](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/be89bfe44782868879f167c7fb325b3e77143491))
* **main:** release 1.8.0 ([d354d6d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d354d6d9bc9e02819aa110fbbb64bc331b48f478))
* **release:** align release state ([36308d5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/36308d59239e45e239e5a7b8fc24433ed41c1ca7))
* **release:** bump to 2.0.9 and document menu tokens ([1c22258](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1c22258518dc619aa27d0ff19ea9c58a04d4dcc4))
* **release:** bump to 2.0.9 and document menu tokens ([1e24635](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1e2463513be8727521b58fe59ac60207474147d5))
* **release:** bump to 2.1.2 ([180826d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/180826dd8cfdcb2ad0c3f081dcd166d3cb4ff5d3))
* **release:** bump to 2.1.2 ([1312c19](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1312c190140c8e3086f97299041be7f81ea917bf))
* **release:** finalize 1.11.4 ([2b6e066](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/2b6e06626a9b79b15dbb5748e447dc1386af449d))
* **release:** finalize 1.11.4 ([f2da99b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f2da99b8fcbb8495417cc1e8d1ef37273fbd2d4e))
* **release:** prep v1.11.5 ([59134c5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/59134c53f751c64762c4dafe248e50cdb6752ecd))
* **release:** prep v1.11.5 ([69eaa16](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/69eaa16a5a615899dac2c27ce9f4093ab9eff47c))
* **release:** prepare 1.11.6 ([82eaacd](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/82eaacd5d9e850611548f86d1855399b9abd03dd))
* **release:** prepare 1.11.6 ([f27c0d0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f27c0d025d47a3b79c7ff093d57f11d38da84d89))
* **release:** prepare v1.11.1 ([13e3deb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/13e3deb768c5d41a57c964b1276841074d2876a3))
* **release:** prepare v1.11.1 ([8a2589f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8a2589fb11bb670f564fc90eee1a0a21a4624e78))
* **release:** v1.11.3 ([6a72f11](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6a72f112cde9374114e9cb3fe17654ebea088b1f))

## [2.1.2] — 2025-09-13
### Added
- Full Typography token set (H1–H6, body, small, text/link/muted colors) with live preview.
- Tokenized Tabs controls (size, paddings, radius, gap, indicator, states) wired to FBM tab components.
### Improved
- Scoped variables under @layer fbm and .fbm-scope; admin assets attached via canonical hook suffix with correct inline order.
### A11y
- Tabs follow WAI-ARIA APG with :focus-visible outlines; radios/checkboxes keep accent-color.

## [2.1.1] — 2025-09-13
### Added
- Two-pane Theme & Design with live preview using real FBM markup.
- New token groups: Typography, Forms, Tables & Cards, Notices.
### Improved
- Admin asset order and screen scoping; variables emitted under  fbm and .fbm-scope.
### A11y
- Kept :focus-visible outlines and accent-color for form controls.

## [2.1.0] — 2025-09-13
### Added
- Implemented Menu design tokens (icon size/opacity, paddings, item height, states, divider) and wired all Theme controls to FBM tags.
### Fixed
- Inline variables are attached to the enqueued handle on the canonical admin hook; tokens affect FBM screens/shortcodes only.
### A11y
- Kept :focus-visible rings and forced-colors fallbacks; radios/checkboxes use accent-color.

## [2.0.9] — 2025-09-13
### Added
- AIW-style Menu tokens (icon size/opacity, paddings, item height, active/hover colors, divider, radius), scoped to `.fbm-scope`.
### Fixed
- Every Theme control now maps to explicit tokens and selectors inside FBM UI; no changes to WordPress admin chrome.
### Dev
- Extended NoBleedSelectorsTest and added ThemeControlToTokenTest & InlineOrderStillCorrectTest.

## [2.0.8] — 2025-09-13
### Fixed
- Fully scoped theming to FBM pages/shortcodes via `.fbm-scope` and cascade `@layer`.
- Corrected enqueue order (admin_enqueue_scripts → wp_add_inline_style on enqueued handle).
- Prevented admin chrome bleed with CI “No-Bleed” selector tests.
### UX
- Kept “Apply theme to FBM interface” label & helper text.

## [2.0.7] — 2025-09-13
### Fixed
- Scoped all theme tokens and styles to `.fbm-scope`, preventing bleed into the WP admin chrome.
- Refactored enqueues to `admin_enqueue_scripts` with correct inline-style order.
- Restored dashboard 3×3 grid under the scoped container with responsive breakpoints.
### UX
- Renamed “Apply theme to admin menus” → “Apply theme to FBM interface” to reflect actual behavior.

## [2.0.6] — 2025-09-13
### Fixed
- Resolved activation “Critical Error” in release ZIP by moving test autoloaders to autoload-dev and building with --no-dev + optimized autoloader.
- Theming now reliably loads only on FBM admin pages via whitelist & correct admin enqueue flow; inline tokens attach to an enqueued handle.
- Restored Dashboard 3×3 grid with responsive collapse.
### Dev
- Added AdminScope helper & optional diagnostics for hook/screen/slug.
- Hardened packaging script to produce production-safe vendor autoload.

## [2.0.5] — 2025-09-13
### Fixed
- Theming now attaches only to FBM admin pages by slug whitelist and the correct admin enqueue hook.
- Inline token CSS reliably prints by attaching to an already enqueued handle.
- Dashboard restored to 3×3 grid at desktop with responsive fallbacks.
### Dev
- Added AdminScope helper + optional FBM_DEBUG_THEME diagnostics overlay (hook, screen id, page slug).

## [2.0.4] — 2025-09-13
### Fixed
- Scoped theming to explicit FBM admin pages via slug whitelist and body class; corrected enqueue order so token CSS always prints.
- Restored Dashboard 3×3 grid with responsive fallbacks.
### Dev
- Centralized AdminScope; guarded get_current_screen timing; reinforced inline-style attachment to enqueued handles.

## [2.0.3] — 2025-09-13
### Fixed
- Theming now applies strictly to FBM admin slugs (fbm, fbm_attendance, … fbm_shortcodes) and to frontend dashboard shortcodes.
- Removed ambiguity from screen-based gating; inline variables reliably attach to enqueued handles.
### Dev
- Introduced AdminScope with explicit slug whitelist and admin body class tagging.

## [2.0.2] — 2025-09-13
### Fixed
- “Apply theme to admin menus” now correctly themes all FBM admin pages by tightening screen detection and enqueue order.
### Dev
- Centralized screen gating helper; ensured inline tokens attach to an enqueued handle; added tests for hook

## [2.0.1] — 2025-09-13
### Added
- Token-driven scaffolding for all core UI elements (inputs, nav, informational, containers, icons), wired to Design & Theme.
- SVG icon system (currentColor) with ARIA-friendly rendering.
### Improved
- Accessible defaults: visible :focus-visible, `@media (forced-colors: active)` fallbacks, `accent-color` for form controls.
### Dev
- Extended token schema with component aliases; docs updated with component map and a11y notes.

## 2.0.0 — Fix & Repair
### Fixed
- Design & Theme settings reliably save via proper Settings API registration.
### Added
- Native WordPress Color Picker for theme colors.
- Tokenized UI applied to FBM admin and public forms (with `accent-color`, focus-visible, forced-colors).
### Dev
- Scoped admin assets loading; strict sanitizer and payload bounds.

## 1.11.7

- fix: Settings API wiring for Design & Theme with tokenized variables and accessibility tweaks.

## 1.5.1-rc.1

- Shortcodes helper examples and docs.

## [1.8.0](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.8.0...1.8.0) (2025-09-12)


### Features

* diagnostics mail test UI and telemetry polish ([948fc48](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/948fc4852fd5e5ae362327c2f4e435bfb4ff3876))
* **diagnostics:** mail test UI, rate-limit headers, and privacy preview ([631d320](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/631d3209995ca01a7a112caf2a62dde3a2c983cd))


### Miscellaneous Chores

* align release-please to 1.8.0 ([d2f62b3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d2f62b35a185ad2b07fb7394da20d59e24ef64fc))
* **main:** release 1.8.0 ([be89bfe](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/be89bfe44782868879f167c7fb325b3e77143491))
* **main:** release 1.8.0 ([d354d6d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d354d6d9bc9e02819aa110fbbb64bc331b48f478))
* **release:** align release state ([36308d5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/36308d59239e45e239e5a7b8fc24433ed41c1ca7))

## [1.8.0](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.8.0...1.8.0) (2025-09-12)


### Miscellaneous Chores

* align release-please to 1.8.0 ([d2f62b3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d2f62b35a185ad2b07fb7394da20d59e24ef64fc))
* **release:** align release state ([36308d5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/36308d59239e45e239e5a7b8fc24433ed41c1ca7))

## [1.8.0](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.7.0...1.8.0) (2025-09-12)


### Features

* add rate limiting, CLI mail test, and cron telemetry ([5ae4598](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/5ae459896283b00997efbd345ab6b0d5bb67dce8))
* add rate limiting, privacy export polish, and cron telemetry ([cdd42d8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cdd42d881038bf63812955a3b7f524629a51debe))

## [1.7.0](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.6.0...1.7.0) (2025-09-12)


### Features

* add CLI parent command and diagnostics report ([29cba38](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/29cba38de37a0be021d6e2dc60b216dfe6c90d97))
* **cli:** functional jobs and retention commands ([3ec12cc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3ec12cc75a6b37700dd909e2ae46a63009160e34))
* **cli:** functional jobs and retention commands ([f179e50](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f179e504c8fa877e055bb12eb4856d387b6eb733))
* **cli:** scaffold parent command and diagnostics report ([7e54d11](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7e54d11a6c5ceac0f80dee4d78afced819b1dddd))

## [1.6.0](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.5.3...1.6.0) (2025-09-12)


### Features

* **cli:** register version command during boot ([fc7843c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fc7843c9903e441f46cb8131336ec2d0fe120f60))
* **cli:** register version command during boot ([8cdab67](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8cdab67ac56c3b6ab2c7cb474d11150150383e20))

## [1.5.3](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.5.2...1.5.3) (2025-09-12)


### Bug Fixes

* **cli:** register WP-CLI version command ([847ee46](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/847ee4686d672a95d1c935a1d12a0a68bf19182f))

## [1.5.2](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.5.1...1.5.2) (2025-09-12)


### Bug Fixes

* **scan:** return explicit check-in status ([335750a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/335750a54f5a6a29ea661a688bcee1d7eeb937ca))
* **scan:** return explicit check-in status ([81cb6cf](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/81cb6cf5aac2fed58aaee7119818b737693593dd))

## [1.5.1-rc.1](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.5.0-rc.7.7...1.5.1-rc.1) (2025-09-11)


### Miscellaneous Chores

* align CI and finalize shortcodes screen ([0b7e1cc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0b7e1cc0f4aeb6a1f209112c4ba67e4b111b21b3))
* release 1.5.1-rc.1 ([d64f57b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d64f57b8e2fe9f90f4d60116a91ddb081bd74982))

## [1.5.0-rc.7.7](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.4.2-rc.7.7...1.5.0-rc.7.7) (2025-09-11)


### Features

* add shortcodes admin script ([14cfbd6](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/14cfbd6bec28b2ad60a75e2d0d242fa995597750))
* polish shortcodes admin ([77bfbf0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/77bfbf0e39edacad04b034d59403cd45a700bdd3))
* **shortcodes:** add helper examples and docs ([29e8fc5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/29e8fc549793b960a43089e1d3323e23c72dddd4))
* **shortcodes:** add helper examples and docs ([4f68ae0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4f68ae0e3a69f67a17b64e9bd48cb6c5308e2b69))
* theme save and diagnostics enhancements ([6be2a19](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6be2a19f1d5fbe2a7aacd75d286df7c39bc6271e))
* theme save and diagnostics enhancements ([7e3b2b1](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7e3b2b1fef8a3f2310ee9350c3b707ef2117f62d))

## 1.5.0

- Theme save fix; Diagnostics mail failures + resend; SMTP/API/KEK health; tests green; lanes=0; stan=0; packaging OK.

## 1.4.0-rc.7.7 — feat(admin): permissions UI and auto-tag workflow

## [1.4.2-rc.7.7](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.4.1-rc.7.7...1.4.2-rc.7.7) (2025-09-11)


### Miscellaneous Chores

* **release:** prepare stable v1.5.0 ([e75482f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e75482f075e80020684f791179458470caf8a785))

## [1.4.1-rc.7.7](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.4.0-rc.7.7...1.4.1-rc.7.7) (2025-09-11)


### Miscellaneous Chores

* **ci:** add release checksums ([7156f1b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7156f1b17cb18c257770d4853c1855a221ea2de5))
* **release:** configure release-automation ([b07cbe7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b07cbe728ccf49bc555cc64fe876af22249be181))
* **release:** configure release-automation ([ba2db9f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ba2db9fecb70ca188111d34474ac1cf5cb30a96c))
* switch release-please to stable 1.5.0 ([526db9b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/526db9bc3bfc83affac3568a5a9bd571cce85310))

## 1.4.0-rc.7.6 — feat(core): centralize capabilities

- feat(core): add canonical capabilities list for reuse across UI and tests

## 1.4.0-rc.7.5 — chore(license): align GPL-2.0-or-later, i18n build, release provenance

- chore(license): align project to GPL-2.0-or-later for mPDF compatibility
- chore(i18n): build .mo catalogs during packaging
- chore(release): add provenance workflow and packaging guard

## 1.4.0-rc.7.2 — fix(pdf): kill closure serialization

- fix(tests): drop process isolation and closures from PDF suites
- fix(tests): stub headers to avoid warnings in shared process
- chore(pdf): bind headers/footers via @page for stability

## 1.4.0-rc.7.1 — fix(pdf): stable receipts and bulk zip

- fix(tests): remove closure serialization in PDF suites
- fix(exports): default-masked receipts gated by capability
- fix(exports): deterministic Bulk PDF ZIP with closed archive
- fix(pdf): letterhead uses @page header/footer for stability

## 1.4.0-rc.7.0 — feat(pdf): renderer, templates and diagnostics preview

- feat(pdf): mPDF-backed renderer with letterhead and receipt templates
- feat(diagnostics): PDF settings panel with preview

## 1.4.0-rc.6.7 — feat(privacy): SAR exporter/eraser and diagnostics panel

- feat(privacy): register WP Privacy exporter/eraser and add Diagnostics → Privacy panel
- chore(cs): raise PHPCS repo memory limit

## 1.4.0-rc.6.6 — chore(cs): tighten lanes and scripts

- chore(cs): tune ruleset (Core + curated Extra), run phpcbf on lanes
- chore(cs): stabilize lanes+repo scripts (summary/source/json; repo ignores on exit)
- docs(cs): update PHPCS-Ignores with new ignores

## 1.4.0-rc.6.3 — chore(cs): scope phpcs lanes and add repo-wide report

- chore(cs): scope phpcs lanes and add repo-wide report (summary+source+json)
- chore(cs): ensure WPCS registered; lanes script prints output reliably
- docs(cs): note lanes policy and repo debt

## 1.4.0-rc.6.2 — fix(diagnostics): retention runner contract

- fix(diagnostics): add retention runner interface and secure admin actions
- docs: note retention controls in diagnostics hub

## 1.4.0-rc.6.0 — fix(cs): clean up email module for PHPCS lanes

- fix(cs): clean up email module for PHPCS lanes
- feat(diagnostics): mail failures + retry, cron telemetry, jobs list
- docs: diagnostics help & SMTP seam (phpmailer_init)

## 1.4.0-rc.4.5.0 — feat(admin/db): dynamic columns, presets, masked detail, visible-columns export

## 1.4.0-rc.4.4.2 — RC4.4.2 — capability and theme save hardening; lanes=0/stan=0/pkg OK.

## 1.4.0-rc.4.4.1 — RC4.4.1 — Forms a11y & validation UX, menu glass parity; lanes=0/stan=0/pkg OK.

## 1.4.0-rc.4.4 — RC4.4 — Admin list-tables + Public forms glass parity; a11y focus/contrast for cells/fields; lanes=0/stan=0/pkg OK.

## 1.4.0-rc.4.3.3 — RC4.3.3 — Menu focus & contrast polish, icon states, perf cap for blur; lanes=0; stan=0; pkg OK.

## 1.4.0-rc.4.3.2 — RC4.3.2 — apply Design & Theme to admin chrome (sidebar/admin-bar) and front-end menus; a11y fallbacks; tests stable; packaging OK.

## 1.4.0-rc.4.3.1 — RC4.3.1 — glass fidelity (layered spec), a11y fallbacks, grid rhythm, focus rings; tests stable.

## 1.4.0-rc.4.3 — RC4.3 — real glass UI (layered cards & buttons), accessible fallbacks, dashboard 3×3 polish. No DB changes.

## 1.4.0-rc.4.2 — 3×3 Dashboard glass UI, theme JSON + tokens polished, a11y fallbacks; no DB changes.

## 1.4.0-rc.4.1 — Theme JSON + CSS tokens finalized; lanes green; a11y fallbacks; no DB changes.

## v1.4.0-rc.4 — Dashboard v1 (Glass)
- feat(admin): Dashboard v1 with glass KPI tiles (registrations, check-ins Today/Week/Month, tickets scanned 7d), 6-month sparkline, and shortcuts
- feat(ui): glass tokens (accent/blur/elevation) with high-contrast & reduced-transparency fallbacks
- chore(i18n): update POT/PO; compile .mo during packaging if msgfmt is available
- docs: PRD/DesignSystem updated for glass; menu deep-link for Scan → Attendance tab

## 1.4.0-rc.3 — 2025-09-09
- fix(packaging): restore dist/foodbank-manager.zip with correct root slug and main file guard
- fix(static): remove duplicate ABSPATH bootstrap warning in PHPStan runs
- fix(tests): stabilize ScanController unit tests via deterministic stubs and header seam
- feat(admin): add Dashboard MVP (manager tiles + 6-month sparkline + shortcuts)
- chore(i18n): compile .mo in packaging when msgfmt is available; otherwise warn without failing build

## 1.4.0-rc.2 — 2025-09-09
- i18n: textdomain loader, strings localized; sample en_GB locale
- Background export jobs: queue + cron worker + secure downloads
- Attendance: Tickets/QR, Scan & manual check-in, Reports & Exports
- Visual Form Builder (CPT `fb_form`) with live preview
- Theme presets incl. High-Contrast; RTL readiness
- CSV/XLSX/PDF pipelines via seams; headers seam; packaging guards
- QA: PHPStan 0/0; PHPCS (lanes) 0/0
- Docs-Revision: 2025-09-09 (Wave RC2)

## 1.4.0-rc.1 — 2025-09-09
- Attendance: Events CRUD, Tickets/QR, Scan & Manual check-in, Reports & Exports (CSV/XLSX/PDF, masked)
- Email Log: Resend (audited)
- Visual Form Builder (CPT `fb_form`) with live preview
- Theme: High-Contrast preset & RTL readiness
- Background export jobs (queue + cron worker + secure downloads)
- CSV/XLSX/PDF pipelines through seams; headers seam
- Tests/QA: PHPStan 0/0; PHPCS (lanes) 0/0; packaging guards passing
- Docs-Revision: 2025-09-09 (Wave: RC1)

## [1.3.0.1] — 2025-09-08
### Maintenance
- Version alignment (composer.json, plugin header, Core constant) with **no runtime changes**.
- Packaging discipline: slug remains `foodbank-manager/`; main file present at `foodbank-manager/foodbank-manager.php`.
### QA
- PHPStan: 0 errors (fast/full).
- PHPCS (lanes): 0 errors / 0 warnings.
- Packaging guards: PASS.

# Changelog

## [Unreleased]

- feat(admin): Dashboard v1 with glass tiles, sparkline, and shortcuts
- feat(ui): glass tokens + high-contrast & reduced-transparency fallbacks
- chore(i18n): update POT/PO; compile .mo during packaging if available

## [1.3.0-alpha.1] — 2025-09-08

Added: SMTP Diagnostics panel; deterministic admin CSS vars; Entry/GDPR gated render; AttendanceRepo BC shims.

Fixed: Capability self-heal & resolver; dashboard export nonce/cap flow; diagnostics POST handling; test stubs for filter_input/get_current_screen/roles.

Security: Consistent sanitization & 255-char clamp for email settings; strict nonces + caps on handlers.

Docs-Revision: 2025-09-08 (Wave U2)

## [1.2.16] - 2025-09-07
- End-to-end form submissions with schema validation, consent hashing, safe uploads, email send & log, and success reference.
- Canonical WP stubs with deterministic shims keep PHPStan and tests green.
- Shortcode assets load only when the shortcode appears; `[fbm_dashboard]` gated by capability.
- Diagnostics Cron Health panel lists hooks with last/next runs, overdue flags, and run-now/dry-run controls.

## [1.2.15] - 2025-09-07
- PHPStan green
- Settings/Theme sanitize + deterministic CSS
- Diagnostics Cron panel
- Forms MVP e2e
- no regressions
## [1.2.14] - 2025-09-06
- packaging guard: enforce single `foodbank-manager/` slug
- diagnostics: duplicate-install detector with one-click consolidate panel
- test-harness URL shims
- no schema changes

## [1.2.13] - 2025-09-06
### Frontend
- Dashboard UX polish: empty-states, a11y labels, focus rings, skeleton loader; admin-only shortcode hint.
### Admin
- Email Templates: live preview with whitelisted tokens; reset to defaults; a11y labels.
- Design & Theme: sanitized token schema, scoped CSS variables, live preview (dark-mode toggle).
- GDPR SAR: streaming ZIP with fallback HTML; masked by default; chunking + README.txt.
### Diagnostics
- RenderOnce badge + per-screen render counts; menu-parents count remains 1.
- Install health detects duplicate plugin copies with one-click consolidation.
### Infra
- Parallel-safe waves with file fences; docs merged from fragments; no runtime loosening.

## [1.2.12] - 2025-09-06
- Trace comments on all admin templates; Diagnostics RenderOnce badge; no behavior changes.

## [1.2.11] - 2025-09-05
### Fixed
- Admin menu de-dup: fallback parent only when core boot/menu not registered; emergency notice suppressed after boot.
### Added
- Diagnostics: "Menu parents registered" row for quick duplicate detection.
### Infra
- RBAC test harness utilities (admin-only vs FBM caps) – ongoing.
### Known
- PHPUnit still has permission-alignment failures in a subset of suites (tracked in Docs/ISSUES - see RBAC alignment items).

## 1.2.10 — 2025-09-05
- feat(bootstrap): boot watchdog and parent menu failsafe with Diagnostics link

## 1.2.9 — 2025-09-05
- test(harness): add WP helpers (transients/options/nonces), reset globals, gate ext branches
- test(notices/menu): align with cap self-heal + admin fallback; de-dup verified
- chore(release): metadata bump only

## 1.2.8 — 2025-09-05
- chore(release): version bump only (no runtime changes)
- docs: note test stubs (WP helpers), retention config normalizer, notices de-dup diagnostics

## [1.2.7] - 2025-09-05
### Build
- bump version to 1.2.7.

### Test
- add WP helper stubs (absint, add_query_arg, wp_salt, …)

### Fixes
- fix(core): normalize retention config (typed)

### Chore
- chore(cs): exclude dist/, docblocks & strict types

### Docs
- document namespace bridge, bootstrap fallback, notice gating, and PHPCS exclusions; add active issues overview.
- PRD/Architecture/Issues updated; diagnostics surfaces notices count

## [1.2.6] - 2025-09-05
### QA
- test: replace anonymous test doubles with named stubs; add deterministic WP function shims for unit tests.

## [1.2.5] - 2025-09-05
### Fixes
- Deduplicated admin menu registration with canonical slugs and Settings page rendering.
- Screen-gated assets and notices with unified `.fbm-admin` wrappers.
- Diagnostics now checks menu slugs and asset gating.

## [1.2.4] - 2025-09-05
### Docs
- Docs revised for Strict Guard Green (Wave v1.2.4).
### QA
- Promoted PermissionsPage to Strict with nonce checks and sanitized inputs.

## [1.2.3] - 2025-09-04
### Fixes
- fix(admin): de-dup menus under canonical slugs
- fix(core): screen-gated assets/notices via helper
- fix(ui): ensure all admin templates use .fbm-admin wrapper
### QA
- test: deterministic WP stubs; remove anonymous classes
### Docs
- docs: stamp Docs-Revision (Wave v1.2.3)

## [1.2.2] - 2025-09-04
### Features
- Dashboard shortcode now accepts type/event/policy filters and provides a capability-gated summary CSV export.
### Docs
- Docs revised for Frontend Dashboard P3 (Wave v1.2.2).

## [1.2.1] - 2025-09-04
### Features
- Dashboard shortcode now shows trend deltas and daily check-ins sparkline.
### Docs
- Docs revised for Frontend Dashboard P2 (Wave v1.2.1).
### QA
- phpstan:fast memory limit increased to 768M.

## [1.2.0] - 2025-09-04
### Features
- Manager dashboard shortcode with aggregated non-PII cards.
### Docs
- Docs revised for Frontend Dashboard P1 (Wave v1.2.0).

## [1.1.10] - 2025-09-04
### Fixes
- Contained admin pages with `.fbm-admin` wrapper and screen-gated assets/notices.
### Docs
- Docs revised for Admin Layout Guard (Wave v1.1.10).

## [1.1.9] - 2025-09-04
### Features
- Permissions admin page: JSON export/import (Dry Run), per-user overrides table, and Reset.
### Docs
- Docs revised for Permissions UX (Wave v1.1.9).

## [1.1.8] - 2025-09-04
### Features
- Design & Theme settings with primary colour, density, font, dark mode default and optional custom CSS applied via CSS variables.
### Docs
- Docs revised for Design & Theme (Wave v1.1.8).

## [1.1.7] - 2025-09-04
### Features
- Saved Database filter presets and per-user column toggles.
### Docs
- Docs revised for Database UX P1 (Wave v1.1.7).

## [1.1.6] - 2025-09-04
### Features
- Admin-only QR check-in links and override reason prompt for Attendance.
### Docs
- Docs revised for Attendance P1 (Wave v1.1.6).

## [1.1.5] - 2025-09-04
### Features
- Read-only Forms Presets Library with shortcode preset support.
### Docs
- Docs revised for Forms Presets P1 (Wave v1.1.5).

## [1.1.4] - 2025-09-04
### Features
- Emails admin page skeleton listing default templates with edit links.
### Docs
- Docs revised for Emails skeleton (Wave v1.1.4).

## [1.1.3] - 2025-09-04
### Features
- Shortcodes builder with masked shortcode generation and nonce-protected live preview.
### Docs
- Docs revised for Shortcodes Builder+Preview (Wave v1.1.3).

## [1.1.2] - 2025-09-04
### Features
- Shortcodes admin page listing available shortcodes, attributes, and examples.
### Docs
- Docs revised for Shortcodes List (Wave v1.1.2).

## [1.1.1] - 2025-09-04
### Features
- Diagnostics Phase 1: environment checks with test email and Repair Caps.
### Docs
- Docs revised for Diagnostics Phase 1 (Wave v1.1.1).

## [1.1.0] - 2025-09-04
### Features
- Settings Phase 1: validated options schema and admin save flow for Branding and Email defaults.
### Docs
- Docs revised for Settings Phase 1 (Wave v1.1.0).

## [1.0.7] - 2025-09-03
### Security/Quality
- Remove PHPCS suppressions from AttendanceRepo; promote to Strict.
### Docs
- CS-Backlog.md revision-stamped for Wave CS-Backlog-11B.2.

## [1.0.6] - 2025-09-03
### Security/Quality
- Harden Database admin page and CSV exports; sanitize filters, whitelist ordering, and mask PII by default. Promoted to Strict.
### Docs
- Architecture.md, README.md, PRD-foodbank-manager.md, and CS-Backlog.md revision-stamped for Wave CS-Backlog-10.

## [1.0.5] - 2025-09-03
### Quality/Security
- Admin Menu hardened; moved to Strict.
### Docs
- Architecture.md, CS-Backlog.md, PHPCS-Ignores.md revision-stamped for Wave CS-Backlog-09.

## [1.0.4] - 2025-09-03
### Security
- Strict guard cleanup (no suppressions in Strict files)
### Build
- PHPStan config modernized (no deprecated options)
### Docs
- CS-Backlog.md and PHPCS-Ignores.md revision-stamped for Wave CS-Backlog-07
- Clarified AttendanceRepo SQL safety, masking defaults, and test coverage across PRD, Architecture, DB_SCHEMA, API, README, CONTRIBUTING, CS-Backlog, PHPCS-Ignores, and ISSUES docs.

## [1.0.3] - 2025-09-04
### Security
- Harden Database admin page and CSV exports with nonces, sanitization and masking; promote to Strict.
### Docs
- PRD-foodbank-manager.md: clarify database export capabilities and masking.
- Architecture.md: note database capabilities and masked exports.
- CS-Backlog.md: move DatabasePage and CsvExporter to Strict.
- PHPCS-Ignores.md: regenerate report.

## [1.0.2] - 2025-09-03
- Harden Permissions page (sanitization, nonces, safe redirects); moved to Strict; no behavior changes.

## [1.0.1] - 2025-09-03
- Harden uninstall script for safe, silent cleanup.
- Resolve PHPCS warnings and fix AttendanceRepo tests.
- Add GitHub Actions for CI and automated releases.

## v0.1.1 — 2025-09-01
