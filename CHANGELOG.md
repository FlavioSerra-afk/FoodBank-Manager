## [2.2.2] — 2025-09-15
### Added
- New two-pane Theme page with accordion controls & style-book preview
- Live CSS variable preview + JSON Import/Export/Defaults
### Fixed
- Fixed settings save (register_setting for fbm_theme)
- Tight Theme-screen asset gating
### Tests
- No new PHPCS/PHPStan issues; tests green

## [1.9.1](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.9.0...1.9.1) (2025-09-14)


### Miscellaneous Chores

* bump plugin version to 2.2.2 ([cf88eb2](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cf88eb206befa4e61e639cbdf3071ba0fc4a0a22))
* release 2.2.2 ([8495d22](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8495d22e7c6ae22bb01a10ceb8d56d191cc8f687))

## [2.2.1] — 2025-09-13
### Improved
- Theme preview script now uses debounced vanilla JS and updates a single `<style data-fbm-preview>` scoped to `@layer fbm` and `.fbm-scope` for live token changes.
### Tests
- Added coverage to ensure preview updates reuse the same style element.

## [1.9.0](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.8.0...1.9.0) (2025-09-14)


### Features

* add live theme preview tools ([eed7d09](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/eed7d09ee2f85aa246df7ed4fb5cd47bc62899f5))
* add live theme preview tools ([e6af184](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e6af184a082604ed26e5edeff1f6f901d43e7cfb))
* add theme token schema and controls ([a333277](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/a33327718cae67f670c3ad1146c769dd131f7189))
* add theme token schema and controls ([2566b7e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/2566b7eab52ed34df9a394aa03a7638cfbb096f2))
* **theme:** add preview CSS variables ([8ddee7d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8ddee7def38ce0a6bf3f734701b27ecdb90fb876))
* **theme:** add preview CSS variables ([c70a169](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c70a169446615b659306aea5fccf326a8b9995e3))

## [1.8.0](https://github.com/FlavioSerra-afk/FoodBank-Manager/compare/1.8.0...1.8.0) (2025-09-14)


### Features

* add background export jobs ([6e3a57c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6e3a57c68c22e4d75bfcee947b8362e2d991317c))
* add background export jobs ([f89e99c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f89e99c5a29947c4244f5033b99d7441c4dafe76))
* add bootstrap failsafe and admin menu ([2307ae4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/2307ae401481f6f11e8f20f6db2a7d79a9bf7c72))
* add bootstrap failsafe and admin menu ([de2baa5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/de2baa536db07ca8252d3cf58290a26f7efa01d5))
* add CLI parent command and diagnostics report ([29cba38](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/29cba38de37a0be021d6e2dc60b216dfe6c90d97))
* add email resend controller ([83d5d27](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/83d5d27bcd1fb991421bcb533191bb488aa4de92))
* add jobs admin UI and mail replay ([8c967fd](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8c967fd8a1f7aecb0f0d55a683ec22408be24217))
* add jobs admin UI and mail replay ([bd0ae6a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bd0ae6a446d569c35b97f2a3c38f82a558eec7e4))
* add jobs capability and version bump ([359d9c8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/359d9c89ff5c97929ac0533a5c01c4740167fb45))
* add rate limiting, CLI mail test, and cron telemetry ([5ae4598](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/5ae459896283b00997efbd345ab6b0d5bb67dce8))
* add rate limiting, privacy export polish, and cron telemetry ([cdd42d8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cdd42d881038bf63812955a3b7f524629a51debe))
* add shortcodes admin script ([14cfbd6](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/14cfbd6bec28b2ad60a75e2d0d242fa995597750))
* add type-safe CsvWriter ([c89f4e7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c89f4e7b8f6620ab062a6d4b87a921b2f67fb05f))
* add visual form builder ([6493203](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/64932032c98c56c8531e645ec2e15a635b7dce40))
* add visual form builder ([f6bcd95](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f6bcd95468b0d418be6a36d95c9b362864309ba9))
* **admin/db:** dynamic columns and export presets ([bf5e8ff](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bf5e8ffa5b0be454c1d6ede54d5763d72f0ab125))
* **admin/db:** dynamic columns and export presets ([562e9c3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/562e9c368f13a9154e3be638e1622afa2d13123a))
* **admin:** add dashboard mvp and stabilize tests ([cbf622f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cbf622f7a2f6bdee2efac54c68d22865710ae4a8))
* **admin:** add emails page skeleton ([e29d8dc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e29d8dc227cba9003ddb2f455065b87fd4a0a37e))
* **admin:** add emails page skeleton ([05e5813](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/05e581361ea906669c2d92f98347dd9afdb5511e))
* **admin:** add shortcodes page ([30d74f0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/30d74f08ae2b4a6f862f2c19553ea3368c09f00d))
* **admin:** add shortcodes page ([bb32f2e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bb32f2e8111e56f81c8f33d24bf1b6d770352198))
* **admin:** adopt WP_List_Table for submissions ([9e3a01e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9e3a01ea9c2ea0eb761c0a704ac1461aed027eb5))
* **admin:** adopt WP_List_Table for submissions ([b2e5d80](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b2e5d80041f2eb6ecc0bbf8c8a0c87a595cbc754))
* **admin:** dashboard v1 with glass tiles and shortcuts ([7dcd782](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7dcd782b18ac7692f2a96f29ae42be22533a8835))
* **admin:** glass dashboard 3x3 grid ([f350e7c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f350e7ca4f6d8816dcc1c5bed29a918551203661))
* **admin:** glass dashboard 3x3 grid ([2859582](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/28595824ae1474859e58f415aca376e1a3a68c68))
* **admin:** live email template preview and reset ([fc07d74](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fc07d740fec4e6051393b05c432a51be4a8cc1f1))
* **admin:** live email template preview and reset ([06f5fbe](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/06f5fbe7a011d5eedcc565c8d86707e8ac260bf2))
* **admin:** permissions manager ui and tag workflow ([5d6e809](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/5d6e8092dbe6d76d60402230087af0266b15aae4))
* **admin:** permissions manager ui and tag workflow ([c16b809](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c16b8099754128bbb491beee28be7cb6d8214972))
* **admin:** resilient menu visibility notice ([be382dd](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/be382dd7fcbadb33b0a603f3a3128ee4e6884a18))
* **admin:** resilient menu visibility notice ([100a3d7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/100a3d7aea7e712ab07c7114844fde01b181d3f8))
* **admin:** trace comments and RenderOnce badge ([d6a63f8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d6a63f810420ea3258a0b335cb9664aaeb373a88))
* **admin:** trace comments and RenderOnce badge ([7a51b8d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7a51b8dce99b18c0357b750feeab81345dd3904e))
* **attendance:** add events CRUD ([3e79695](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3e796959ddc5f9c3043a01faea0059f1b8446896))
* **attendance:** add events CRUD ([cea2de0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cea2de0194bc6afff285da599c1bd8aabf251fdd))
* **attendance:** add reports and export tooling ([4712782](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/471278264e5fefa3a5ddbb6f3d43471107790a15))
* **attendance:** add reports and export tooling ([f59e87a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f59e87a3100e9403ccf133fc466f5191958103a6))
* **attendance:** add scan and manual check-in workflow ([763636c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/763636c5b9e7617a303f681547041fc6375dc7f6))
* **attendance:** add scan and manual check-in workflow ([c8087fb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c8087fbad4284ca15fa006ec0f2fba882b902d49))
* **attendance:** admin QR check-in and override reason ([66c36f9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/66c36f979be71c488805bf76bed640533f772b9c))
* **attendance:** admin QR check-in and override reason ([96222e5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/96222e5e2c64fdd5279e189fc81e3ad5db2a137a))
* **attendance:** issue QR tickets and email ([fbe980d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fbe980db81c893c7bfcca26fdee63f67f340ed0c))
* **attendance:** issue QR tickets and email ([d7d98ed](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d7d98eda2ac7ee78502c2139a3c8a987c45c3456))
* **auth:** deterministic capability resolver ([da09769](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/da097690cae4ad4df820ae3aa480b93f56932e1a))
* **auth:** deterministic capability resolver ([9231aab](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9231aab649c3bdda34ee75a4856a961035f63278))
* **auth:** self-heal admin capabilities ([d255fcb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d255fcb518ab1a111e8d657d6b8db4543bdfebe7))
* **auth:** self-heal admin capabilities ([1d49b87](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1d49b87627c1c75705020282b9be402ffcce3a8e))
* **bootstrap:** add boot watchdog and menu failsafe ([af1afde](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/af1afdeab546ea97af011784742b40d54f2f6e9d))
* **bootstrap:** boot watchdog and menu failsafe ([77783e4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/77783e466f9830e5359f1296355f27d6b1b744ab))
* **cli:** functional jobs and retention commands ([3ec12cc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3ec12cc75a6b37700dd909e2ae46a63009160e34))
* **cli:** functional jobs and retention commands ([f179e50](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f179e504c8fa877e055bb12eb4856d387b6eb733))
* **cli:** register version command during boot ([fc7843c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fc7843c9903e441f46cb8131336ec2d0fe120f60))
* **cli:** register version command during boot ([8cdab67](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8cdab67ac56c3b6ab2c7cb474d11150150383e20))
* **cli:** scaffold parent command and diagnostics report ([7e54d11](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7e54d11a6c5ceac0f80dee4d78afced819b1dddd))
* consolidate duplicate installs logging ([1160b8f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1160b8f9011c52ccc6742d90c3995c57f5c179a4))
* consolidate duplicate installs logging ([b1233bc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b1233bcf9719002254788c9f5e6a8f2d158e3838))
* **core:** centralize capabilities list ([6af52d2](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6af52d28f740b8b50f733799a27c46577ba29660))
* **core:** centralize capability list ([4fed642](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4fed6422f1de80ab80f36fd8ed30166918f16cd8))
* **core:** detect duplicate installs and consolidate ([e4a0f8e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e4a0f8e45df18b0df70e9302d46d43da7a78f4e2))
* **core:** detect duplicate installs and consolidate ([e09e668](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e09e668b529a7d161b97028152adb84937d82c73))
* dashboard compare mode and filter persistence ([3203cc6](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3203cc6d58604af73ad762bb369f0b0214e3932c))
* dashboard glass UI and tokens ([e524de8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e524de8db3c430a573c4a52c7fa771c7d59bf424))
* **dashboard,theme:** 3×3 glass dashboard + tokenized CSS with a11y fallbacks ([fa65559](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fa6555957de63ef66d8b8d72442b1ff8e744590d))
* **dashboard,theme:** 3×3 glass dashboard + tokenized CSS with a11y fallbacks ([adc2e5a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/adc2e5a7825c264a1f6a01f7307e06debc44feaa))
* **dashboard:** add filters and CSV export ([c220941](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c22094192bc8a3568e4a99297e22e9af7cc097f6))
* **dashboard:** add filters and CSV export ([bb2f6fb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bb2f6fbfe5217bf1ed52f4316638b7f1e69118bf))
* **dashboard:** add manager dashboard shortcode ([6f7415b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6f7415b23a64c03a272f4030d1a41192e5323b55))
* **dashboard:** add manager dashboard shortcode ([42b0d80](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/42b0d800babad6428bb86147c77c22fa0b963228))
* **dashboard:** add trend deltas and sparkline ([ae9df87](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ae9df8783cfab972aa8f16bda5f543f6fac12214))
* **dashboard:** add trend deltas and sparkline ([3519aca](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3519acaf96e468ba8656e3aa19743a79038668ec))
* **dashboard:** improve public dashboard UX ([4de24ce](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4de24ce896b86b66d9f6d2452bde802e019c0dc4))
* **dashboard:** improve public dashboard UX ([175326b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/175326bf2332bd9ea08b32aae9e7e14f198f8171))
* **database:** add filter presets and column toggles ([8778521](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/87785219db7bf57ecd36effe54d96dbdbece76a3))
* **database:** entry detail (masked by default) + PDF export with safe fallback ([d497cb5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d497cb545a6b208a1bd15a82682ac69f5346e5be))
* **database:** entry detail (masked by default) + PDF export with safe fallback ([be8552c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/be8552cab4dcbbdcdb149acde926557b4791c273))
* **database:** filter presets and column toggles ([3eab69c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3eab69cef64889bd5bbcc0192a566d4b4cebb9cf))
* diagnostics mail test UI and telemetry polish ([948fc48](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/948fc4852fd5e5ae362327c2f4e435bfb4ff3876))
* **diagnostics:** add cron health panel ([d669f9d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d669f9d881e657169f220dac1c8cca72382c9dfc))
* **diagnostics:** add cron health panel ([12688ac](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/12688acdab1e8aa579808a2bab1b623b8a7d8be7))
* **diagnostics:** add environment checks and repair caps ([8240945](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8240945ec397dc4d7098a5025eee7649b3a1bb31))
* **diagnostics:** add environment checks and repair caps ([a2214e0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/a2214e0a82b52bc75047943ec6f492efb9bae989))
* **diagnostics:** add mail failure retries and cron info ([d606312](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d6063121018556ac7e490adc9ee492969eed8f67))
* **diagnostics:** add SMTP diagnostics panel ([ae3a3fd](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ae3a3fd06883f9a19f6651fb851f2a6002ef7330))
* **diagnostics:** mail failure retries and cron telemetry ([59aba38](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/59aba387a67c867c7137281118178b1aba1397ae))
* **diagnostics:** mail test UI, rate-limit headers, and privacy preview ([631d320](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/631d3209995ca01a7a112caf2a62dde3a2c983cd))
* **diagnostics:** report notices render count ([8205e80](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8205e80928f10fb16fb2b9fa9bd49b2a492fb1ca))
* **diagnostics:** report notices render count ([969aaa3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/969aaa36f7ed2b268e4406cf7a5d4a496a4b33da))
* **email:** templates CRUD with renderer and diagnostics ([829f2ea](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/829f2eaf597f188bec5bfe58409cdd6561607d8c))
* **email:** templates CRUD with renderer and diagnostics ([8207344](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8207344bcd29ff9c0fede7d1e9d8fd867ba4db22))
* **exports:** stream SAR ZIP with HTML fallback ([dcf4c0c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/dcf4c0cfc0086317ddd12430c72cf57bc2745700))
* **forms:** MVP Form Builder with per-form CAPTCHA + schema-validated submit ([8f39bae](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8f39bae3bca2ed483fa6c01a5da6553fbf6f3a11))
* **forms:** MVP Form Builder with per-form CAPTCHA + schema-validated submit ([d3d591b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d3d591b1236f9427c0770cb0286679c461db0429))
* **forms:** presets library and shortcode support ([ec80558](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ec80558d4406875ec6e48f303ad0362d3605f4ab))
* **forms:** presets library and shortcode support ([3b6eaee](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3b6eaee05823ee0de481456c7804caebe97b5cfb))
* **forms:** wire submission pipeline ([b91fd2c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b91fd2c673dc6f7e337a7d692f7602bd7f8ccf68))
* **forms:** wire submission pipeline ([e857313](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e857313e01cfef7414596e9ce973fd5a8afdbf94))
* **forms:** wire up forms builder MVP ([6e0bba8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6e0bba8ec2fcf153296624dc9ca2b41c6d036596))
* GDPR SAR exporter/eraser with diagnostics privacy panel ([ad24b90](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ad24b901d89904716214343757cf2aacdc3ee063))
* **gdpr:** retention/anonymisation job + cron diagnostics + settings ([e3103b9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e3103b9ff5f3f81c8eb9cf9036b9ab1061f2a456))
* **gdpr:** retention/anonymisation job + cron diagnostics + settings ([f0c74a9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f0c74a95ae5d54dcb90a47a46c07fc3484dc2b30))
* **gdpr:** SAR export (masked by default) + diagnostics crypto/SMTP/env ([e26c4f2](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e26c4f206140c07c1cc951d677d17c78f72b3068))
* **gdpr:** SAR export (masked by default) + diagnostics crypto/SMTP/env ([d7e55a1](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d7e55a1ae5f5dc3a640a90a2a091748becad4191))
* **glass:** layered glass cards/buttons + a11y fallbacks ([d8cb94a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d8cb94afd1136e5990da417c41eb2a0570072140))
* **glass:** layered glass cards/buttons + a11y fallbacks; dashboard 3×3 polish ([20b65c4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/20b65c4f4290fbad31bfff849a6ca99ed538b69b))
* improve forms a11y and glass menus ([223564c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/223564c3675fae7a09a3f3747bf1afe848e79b3f))
* **install:** consolidate duplicates on activation ([012d910](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/012d910ad0858936163c604cf3310569323596f3))
* **install:** consolidate duplicates on activation ([9fd054c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9fd054cb97b9a140c5bffa9f018704cc4ab9d18b))
* **installer:** one-click duplicate cleanup ([cbdafd1](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cbdafd15340879eefe7d1bbff9bbf21cdbeafe3e))
* **installer:** one-click duplicate cleanup ([c74fe8b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c74fe8b11a8bd040a1e78a746ce51e9194e9c654))
* **jobs:** add manage jobs capability ([34e2022](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/34e2022bad0b6cb198f20ba13e7159fcb48e506b))
* **jobs:** enforce capability checks and add caps doctor ([f606029](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f60602901b50dbcffed0b454e3400b0dff88150c))
* **jobs:** enforce capability checks and add caps doctor ([d39e617](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d39e6174da237ce6a1369fe1349379d327bafbb1))
* **mail:** seed template renderer with token whitelist ([89bc455](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/89bc4555ab587de0deac08d7da983284d260b177))
* **mail:** seed template renderer with token whitelist ([f777a5f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f777a5f055299296ba03ef75a05c34a118c54aae))
* mPDF PDF rendering and diagnostics preview ([b503b57](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b503b57b0881bc2f4a230e338c7cd93e8ed4d20b))
* **permissions:** add JSON import/export and per-user overrides ([4156db3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4156db36c98f410841b4426567ccf8c2935ea2b0))
* **permissions:** JSON import/export with overrides ([4b10e76](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4b10e76e74a863429d105ef26d2d9cf1c593bd9c))
* polish shortcodes admin ([77bfbf0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/77bfbf0e39edacad04b034d59403cd45a700bdd3))
* **security:** per-role scan throttle and admin settings ([af558d3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/af558d3e4d42f5095a579be98ffa3f7b3d269f6a))
* **security:** per-role scan throttles and admin settings ([c9f495e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c9f495e947318cbaef9a9088e19c65d2c6c184be))
* **settings:** add branding and email options with validated save flow ([614e002](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/614e002e82e28a05c06bd55a6ed1bd614b1a72fe))
* **settings:** add branding and email options with validated save flow ([aa25204](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/aa25204a805f34d34a79dc9b4fff44a599aec51d))
* **shortcodes:** add builder and preview ([d4f4411](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d4f4411fcf22a3f5ed8c1aa81252ca277dba79ac))
* **shortcodes:** add builder and preview ([fcc0a6e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fcc0a6e81b7d9fd92bcde9d2b2f236cd3f0ea3ee))
* **shortcodes:** add helper examples and docs ([29e8fc5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/29e8fc549793b960a43089e1d3323e23c72dddd4))
* **shortcodes:** add helper examples and docs ([4f68ae0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4f68ae0e3a69f67a17b64e9bd48cb6c5308e2b69))
* **shortcodes:** enqueue assets per shortcode ([6678780](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/667878028e48b30cb6d201c505c565f57a40de37))
* **shortcodes:** enqueue assets per shortcode ([8328241](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/83282416a602a542f4bb7e0197bc1aa05abf8400))
* theme save and diagnostics enhancements ([6be2a19](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6be2a19f1d5fbe2a7aacd75d286df7c39bc6271e))
* theme save and diagnostics enhancements ([7e3b2b1](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7e3b2b1fef8a3f2310ee9350c3b707ef2117f62d))
* **theme/menus:** RC4.3.3 focus-visible rings, contrast & perf caps ([b4cb045](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b4cb045a50c4b65871b65cfc81b53dd3d0712866))
* **theme/menus:** RC4.3.3 focus-visible rings, contrast & perf caps ([840fea3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/840fea3cac575179a4efa24159e8583f3407a660))
* **theme:** add design & theme settings with custom css ([9c4523a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9c4523a3a105af60c1bd44e333e7a76bd5ffaeb8))
* **theme:** add design & theme settings with custom css ([1316cde](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1316cde9d2052d859e88091812d9dd229ac8577c))
* **theme:** add high-contrast preset and RTL controls ([81bfa37](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/81bfa3743cc99efbeafa41aee1498c3064563fab))
* **theme:** add high-contrast preset and RTL controls ([2a6c317](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/2a6c317ef97fff432ee0ce2769573d719d98e796))
* **theme:** add live preview and scoped tokens ([b8d9c4e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b8d9c4ebdd8de6b9962fedf1082ad06f1b5228e6))
* **theme:** add token validation and preview ([b15efe2](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b15efe2a13144d8db0de15bb34e7d1dad328780f))
* **theme:** add token validation and preview ([3741ba4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3741ba4690b49150494e43cdeb0b1ed12193caa2))
* **theme:** add typography and tabs tokens ([688bcb9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/688bcb98374a2d14f0d24061e8d1702c61f3a09c))
* **theme:** debounce scoped preview style injection ([7511687](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7511687c3fef8968abc51c07932bb9b99c4a896d))
* **theme:** debounce scoped preview style injection ([df043d4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/df043d4726d7f3005894fc44bd9c7c90d34b53c1))
* **theme:** Design & Theme tab (Admin + Front-end) with Glass/Basic × Light/Dark/HC, blue accent, and match-front-to-admin ([1f3d7d4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1f3d7d4f5c55e90c7178845be270d42d58b15b0b))
* **theme:** Design & Theme tab (Admin + Front-end) with Glass/Basic × Light/Dark/HC, blue accent, and match-front-to-admin ([7772769](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7772769bb593e7b343d41210d0bc90c35c335bfd))
* **theme:** emit preview style via data attribute ([200431d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/200431db524d552eec3c1aa378fc0cec968ad5b7))
* **theme:** emit preview style via data attribute ([d4bc74b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d4bc74b030223b3be38a3b804c265060a152de26))
* **theme:** JSON presets + CSS token layer + glass utilities ([44eed95](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/44eed9558ff738dfb735e45f0c8c358e74202395))
* **theme:** JSON presets + CSS token layer + glass utilities (admin & front) ([5f8f1fb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/5f8f1fb5b8c86c46a58443ea4aa544cf205b90ce))
* **theme:** map ui components to tokens and svg icons ([6b73852](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6b7385220a14057113ff4f4ff92bc308e42cbeb0))
* **theme:** RC4.3.2 apply glass tokens to admin chrome & site menus ([1b7daf2](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1b7daf22ed7d8317a0a28e0035880c28193d62e8))
* **theme:** RC4.3.2 apply glass tokens to admin chrome & site menus ([8aca50d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8aca50dbeec1f7cfeaea3e6835d5fcc4424eccf6))
* **theme:** RC4.4 admin tables + public forms glass parity ([5b1f181](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/5b1f1815d97ac73eca9b23973b99d8ea1e14fb4a))
* **theme:** RC4.4 admin tables + public forms glass parity ([4b4d68f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4b4d68f244b6939d5da2c8b6326d89914fa0203c))
* **theme:** scope menu tokens and design controls ([0d85f53](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0d85f53fb4fb3ae4c62b87f6b556d38ae9612b70))
* **theme:** token-driven components and icon system ([6b90e6d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6b90e6d6ad1cb079d439b3cdb9db604091b8bac2))
* **ui:** RC4.3.1 glass fidelity + a11y fallbacks; stable grid; lanes=0 stan=0; pkg ok ([0dab641](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0dab64156c57095e681551b5b0a0d69e12ea44fa))


### Bug Fixes

* **admin:** canonical menu slugs and gated assets ([c87e7a7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c87e7a7056091d9891523592469e18e964ca3d4d))
* **admin:** canonical menu slugs and gated assets ([1e3377d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1e3377d30e0629dc181f9f38c63ca1e6a616a7be))
* **admin:** de-dup menus, canonical screen gating, Settings render; add diagnostics checks ([4cc06a5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4cc06a5460ad1011b786f838f966b5f495b43141))
* **admin:** de-dup menus, canonical screen gating, Settings render; add diagnostics checks ([96d4702](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/96d4702c391b3ed372a5d8970610334473181eca))
* **admin:** ensure admin pages render once ([3a3ad40](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3a3ad402dca116938bc4ca221b735c724807b406))
* **admin:** guard admin screens to render once ([cb2986a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cb2986ab6d76b6444e96e214bf1f99d862437c87))
* **admin:** guard layout and gate assets ([49527c0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/49527c0c54f0eed250ee8a28384799f0509280b0))
* **admin:** scope theming and restore dashboard grid ([7b369a8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7b369a85611bd46b1077411eae1cd68b39848dfd))
* **attendance:** add camelCase shims for AttendanceRepo ([7d03a4e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7d03a4ea5980491f73b8cce4ab376ba7a2ba99d9))
* **attendance:** add camelCase shims for AttendanceRepo ([56145c9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/56145c9a918b010342ee9dd25ddbff9610160f37))
* centralize CSV writer to remove fputcsv deprecations ([e0cb688](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e0cb688aca19cdabe767e2ca52a5929e79caa203))
* **cli:** register WP-CLI version command ([847ee46](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/847ee4686d672a95d1c935a1d12a0a68bf19182f))
* **core:** finalize v1.11.3 with quiet i18n and header parity ([f7b96fc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f7b96fc564bc4450d9491dafd749c522eee7fda2))
* cut next stable on 1.5.x ([b78414a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b78414a94f172b12188bc03a3bebce9f2dea2aa3))
* cut next stable on 1.5.x ([2349f75](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/2349f758ba0c33027dc347a545f6efac760ada73))
* **diagnostics:** implement retention runner and finalize diagnostics hub ([7fe935a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7fe935a616a385a8a00ab98ae9df07ddf95b2080))
* **exports:** add header seam and harden in_array checks ([82db575](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/82db575ae394deafde54b35070e01306bf1f3076))
* **exports:** add header seam and harden in_array checks ([c97c80b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c97c80b2ae15f72fd22af6b258dd29e53402f0d2))
* harden theme save and capability checks ([45f99a8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/45f99a89a7aaed7d5fed8ad8d59abc13981b2fc8))
* **menu:** guard fallback parent and surface duplicates ([022b1d6](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/022b1d65c93e330ef33bd4317d1edf87c0a6a0f0))
* **menu:** guard fallback parent and surface duplicates ([97940de](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/97940de8f1a25a2b3715ceeb1c15d4f49e844778))
* **package:** guard main file in zip ([1351f8e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1351f8ee03c189f218424dc5c44189a69a58de55))
* **package:** guard main file in zip ([a288d9f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/a288d9f00a122afdc8cd1563c427d35eff93ec75))
* **pdf:** kill closure serialization and bind [@page](https://github.com/page) headers ([c48a66a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c48a66a62415a0bd3a8baa918aacb90ebc29c6bf))
* **phpcs:** tidy FormSubmitController lane ([cba108a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cba108accd038b83e1c98398c0feb72f056c2e02))
* **phpcs:** tidy FormSubmitController lane ([edfeef9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/edfeef93e7e897975e5c0fa833edae7de476d2a3))
* production autoload and FBM admin theming ([6891131](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6891131ac13fa9ad7b2b41cf53fba1cebe33f751))
* **rest:** enforce arg validation and nonce checks ([05233c1](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/05233c1a67c8087d8b740b6e1621c20bdd00f674))
* **rest:** normalize error contract ([060d39b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/060d39ba1d63d0f79a2ea22b8139053a7be737a7))
* **rest:** normalize error contract ([9b567cb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9b567cb2e61a4d6705074341e91cab0818dd0ae3))
* **scan:** return explicit check-in status ([335750a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/335750a54f5a6a29ea661a688bcee1d7eeb937ca))
* **scan:** return explicit check-in status ([81cb6cf](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/81cb6cf5aac2fed58aaee7119818b737693593dd))
* scope theming and restore dashboard grid ([95403ba](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/95403ba594cfaa150c2af2ccb77b3681b6f4d9c8))
* **settings:** sanitize inputs and stabilize theme CSS ([faffa0a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/faffa0a32103e4dbb235c86fb38431027ee8fa79))
* stabilize PDF exports and bump to 1.4.0-rc.7.1 ([9767f0f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9767f0f0741c5b129b789971d7ca7647b6cc6d86))
* **theme:** apply admin theme across FBM pages ([78317ce](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/78317cec906f7aa7b0cfb504dfe9dbb7283997fd))
* **theme:** inject preview vars globally ([1f01ecf](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1f01ecff6dd690e0b3421a2825022cba6338c1c3))
* **theme:** inject preview vars globally ([3fc42a5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3fc42a5bd5b0626e7cd025d90e727c0ccdb33f3c))
* **theme:** isolate plugin styling scope ([56b2272](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/56b2272fb41f93c8e1dd4b79fb1863465065de71))
* **theme:** isolate plugin styling scope ([a09da6e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/a09da6eed82dbd8ca034a86edc0bdcd52611b3a1))
* **theme:** register fbm_theme option with color picker ([7c8880c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7c8880cee15d28ce49e94c907862dd33e1339995))
* **theme:** register option and add color picker ([f93712b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f93712b61efb420dbbb608111c04ab622db27d80))
* **theme:** register settings API and tokenized variables ([8fb5df3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8fb5df3be4b6b217c761d328c4217f793aeb2b50))
* **uninstall:** multisite cleanup and csv export hardening ([d8c811c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d8c811cf9ba969b780cbfba9b51a00798c9a2ada))


### Miscellaneous Chores

* add errors-first health report ([c39bf99](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c39bf99146691448f82eca91a097c3aa5af3a348))
* add errors-first health report ([bcfdd0a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bcfdd0a1c8fd5b79b5075bc6279529b93e9e070d))
* add staging smoke test report ([57732dc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/57732dc62113a56abc110d2ebfdda3e0e7017098))
* add staging smoke test report ([17acb76](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/17acb7620856af3a8de6b0a37e2f096196579c0d))
* add staging smoke test report ([95b2f94](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/95b2f944192fdd9b4e941af26f04a2a96092f906))
* add staging smoke test report ([a900b7c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/a900b7cabe2be12fb980625f3ab79bd0887045a9))
* align CI and finalize shortcodes screen ([0b7e1cc](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0b7e1cc0f4aeb6a1f209112c4ba67e4b111b21b3))
* align release-please to 1.8.0 ([d2f62b3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d2f62b35a185ad2b07fb7394da20d59e24ef64fc))
* **autoload:** wire FBM namespace and stable WP stubs ([6ed75ab](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6ed75ab5eb26377855d68913ae460266f22e688c))
* **autoload:** wire FBM namespace and stable WP stubs ([9ef71df](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9ef71df85b0e49f737f9ffb5bfa6ceec33fc2ad4))
* bump readme version ([8dd44a5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8dd44a5e50c442dafdc7b3fe30ae076efa566eb2))
* bump readme version ([44b0d86](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/44b0d8646df1ad1d3c7944df78501c5025d9b5e9))
* bump to 2.1.5 ([9c26e0f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9c26e0f15f05ee8866d79787c7c79f8786d6f337))
* bump to 2.1.5 ([76a8b92](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/76a8b926bf6fce98b27634fbd339b9e756807348))
* bump to v1.3.0-alpha.1 ([8e1a204](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8e1a204161aa3d4fc9e0dc741fb42d6c700e294e))
* bump to v1.3.0-alpha.1 ([009c7f7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/009c7f785c2cebc7e55407e7f8d8b3c634a68bac))
* **ci:** add release checksums ([7156f1b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7156f1b17cb18c257770d4853c1855a221ea2de5))
* **cs:** add PHPCS lanes script and CI check ([411470c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/411470c1b923cbaf2cbb77c897df1f47677af815))
* **cs:** align Settings and Retention with PHPCS ([21d3856](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/21d3856bd40631c996f7de7db4b57d537a898380))
* **cs:** clean up PHPCS issues in settings and retention ([c99f128](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c99f1288c4e59aa296d5475130f18019361bcc78))
* **cs:** clean up PHPCS issues in settings and retention ([c36a6fe](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c36a6fe5ce10b0787f96520c2eb8db04ae48c79f))
* **cs:** fix phpcs warnings ([bc63fc8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bc63fc8e53d87d2ad3b8d0e5d1d1fb9f65e213ea))
* **cs:** promote PermissionsPage to strict ([d7d22fe](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d7d22fe84b9ffd3d90485aad34e66ad88420fbeb))
* **cs:** promote PermissionsPage to strict ([0839e33](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0839e3367a60d89f30578e00b3aea7066762edf8))
* **cs:** refine phpcs lanes and harden AttendanceRepo queries ([97172a4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/97172a4d84a4a48b3311e896b98f8bc2f28311f4))
* **cs:** remove attendance repo ignore ([c8f9afe](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c8f9afe974b8dc76aa6e47789415f358ca91a0d1))
* **cs:** remove attendance repo ignore ([8ba4ced](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8ba4ced7a776bbc1faf714e06141c38a710dd76d))
* **cs:** sanitize inputs and escape outputs ([ad642f2](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ad642f2fd39945245085882350ccb9e41f1cb11f))
* **cs:** sanitize inputs and escape outputs ([381e407](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/381e40785ee71a9c54ed517352ca3aed200b6b10))
* **cs:** scope phpcs lanes and add repo-wide report ([7adc44e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7adc44ee0ba2f2c761e473ff81ec38605b06b4f8))
* **cs:** tighten PHPCS lanes and release 1.4.0-rc.6.6 ([9522cd8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9522cd82bbf93dcd422366401c6b90bafeb00162))
* drop binaries and compile translations at build time ([c04c446](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c04c44681aa6c1a02ebb2a6357b157391e868d75))
* drop binaries and compile translations at build time ([fa21b73](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fa21b735c83e6548cb72f28564fb159d42b0859e))
* finalize 1.11.2 release ([38a53ae](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/38a53ae62a37039303c2b198c0bfa74926f0815c))
* harden throttling and diagnostics ([f3d135d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f3d135d80d6733a2bce0ff966c8d4526f2ff8568))
* **main:** release 1.4.1-rc.7.7 ([fc218c1](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fc218c1ae6f061aee598a8ddf94563b2aa5439f6))
* **main:** release 1.4.1-rc.7.7 ([a92f7c6](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/a92f7c62e712fe26d9b862bcbfb244fd6b4ea746))
* **main:** release 1.4.2-rc.7.7 ([7256f10](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/7256f10ec689d97fa63d0abf042814b182aa995d))
* **main:** release 1.4.2-rc.7.7 ([b8f7ad0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b8f7ad034ede9d2a8655886e8028957e98b5fb24))
* **main:** release 1.5.0-rc.7.7 ([3ae5e22](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3ae5e22552186bcc70179620f9826f928b0421a2))
* **main:** release 1.5.0-rc.7.7 ([1f45770](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1f457701f0ee0b516177c83b22b1c6e7104cc335))
* **main:** release 1.5.1-rc.1 ([deae1c8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/deae1c8150807d9b9130822f794deceb681b1e0d))
* **main:** release 1.5.1-rc.1 ([14486af](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/14486afa61b5be743fb2e224a74d1b3a95583e97))
* **main:** release 1.5.2 ([f559945](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f559945e011671bb9b0721355f86259175d53fa4))
* **main:** release 1.5.2 ([bd9a63f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bd9a63f7d46c2a6d1062e03933d2247301e5b623))
* **main:** release 1.5.3 ([fee468c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/fee468c475e74c88f6c626c6d2c107f8efe9a006))
* **main:** release 1.5.3 ([6f10b66](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6f10b665770aa3609302e903f7de09e8e1155b8a))
* **main:** release 1.6.0 ([1c43b4d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1c43b4dc209977f223c4dc9dc134dd8b545617fe))
* **main:** release 1.6.0 ([4b8f4b8](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4b8f4b8e295a67c6a820e72bb0de61d4cfb990be))
* **main:** release 1.7.0 ([9742e27](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9742e27e447793a3bc98c064631d02c4e34d39ff))
* **main:** release 1.7.0 ([e911b69](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e911b69fb67d0a2167dc436d84831bf9e0ce60eb))
* **main:** release 1.8.0 ([07e3b4c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/07e3b4c68e614f933fcc0b49619738b30a1f8306))
* **main:** release 1.8.0 ([9c887af](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9c887af187e516de451f7a6e58cce19633c8bd1f))
* **main:** release 1.8.0 ([be89bfe](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/be89bfe44782868879f167c7fb325b3e77143491))
* **main:** release 1.8.0 ([d354d6d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d354d6d9bc9e02819aa110fbbb64bc331b48f478))
* **main:** release 1.8.0 ([2ae8ff9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/2ae8ff932cc3ac9b556debe741e381874ccad2e9))
* **main:** release 1.8.0 ([f80c530](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f80c5302b7866f9b4a7e252bf0b67de3a218497f))
* normalize admin page tests ([275f3f7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/275f3f744bf4eec8718b1f391a83d894c59eeb89))
* **phpstan:** void admin notice callbacks ([615d0ca](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/615d0cad29268610f998fa49390a7ef6ed19d233))
* **rc4.1:** lanes 0/0 + theme save path harden + a11y fallbacks ([e1ffb59](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e1ffb5982557f531c5cf00e329fbfb10052dcb55))
* **rc4.1:** lanes 0/0 + theme save path harden + a11y fallbacks; bump 1.4.0-rc.4.1 ([875c4a4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/875c4a48d5b1eafa1a5d5e5f9913747480bde0b5))
* refresh diagnostics report ([61ab2c4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/61ab2c49b3dbf02cace720d05190f0c0177c900d))
* refresh diagnostics report ([686defd](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/686defd3420148475fffa48500ac47c794e971bb))
* refresh QA report ([bec96ba](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bec96ba1cf51d828a77e44df9a49016097c2ec50))
* refresh QA report ([4326db1](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4326db1f54ab83500fcefc738fcec0beba420ac8))
* release 1.4.0-rc.6.0 with diagnostics docs ([0d717d9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0d717d9d0ac529cacfaf7430c5740d9e6607bdcd))
* release 1.5.1-rc.1 ([d64f57b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d64f57b8e2fe9f90f4d60116a91ddb081bd74982))
* **release:** align GPL-2.0-or-later and add provenance workflow ([bc655a9](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bc655a9b7287222c091ad1951c53069df3e23ca6))
* **release:** align GPL-2.0-or-later and add provenance workflow ([073ff8e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/073ff8e244ba8781c6804b4d6a2406576da866ba))
* **release:** align release state ([36308d5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/36308d59239e45e239e5a7b8fc24433ed41c1ca7))
* **release:** bump to 1.4.0-rc.4 + package ([2d4013a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/2d4013a6b8fc2dc0d1387c9321995a31ed81a241))
* **release:** bump to 1.4.0-rc.4 + package ([5404111](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/5404111f48ae1fabfe68df6bf950d4c964a96500))
* **release:** bump to 1.4.0-rc.4.4.2 ([8e594be](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8e594be8f0ed17b30b0b80e77568bf58fe51624c))
* **release:** bump to 1.4.0-rc.6.0 ([5568e69](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/5568e695cf28962727645fa05beb96392b6930b4))
* **release:** bump to 1.4.0-rc.6.2 ([06fecae](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/06fecaed1057ce929137eb7a7df14e8b8333566f))
* **release:** bump to 1.4.0-rc.6.3 ([3148e93](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3148e938dd53c6f1e0309ffe344c1527b9a7ad63))
* **release:** bump to 1.4.0-rc.6.6 ([d360ba0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/d360ba0c03279f9e1ede51fec3afe1640fc08798))
* **release:** bump to 1.4.0-rc.6.7 ([22efb48](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/22efb482ffb4674deb32c302f6eabb5332af8f38))
* **release:** bump to 1.4.0-rc.7.0 ([ceef07e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ceef07e632bee34120d31d83cc92d450726b34a0))
* **release:** bump to 1.4.0-rc.7.1 ([0f64465](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0f644659feefee3a78dfada8aac75bcab921099c))
* **release:** bump to 1.4.0-rc.7.2 ([4a443a4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4a443a47688d769d3e3fd4b45272ae24d881aa39))
* **release:** bump to 2.0.9 and document menu tokens ([1c22258](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1c22258518dc619aa27d0ff19ea9c58a04d4dcc4))
* **release:** bump to 2.0.9 and document menu tokens ([1e24635](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1e2463513be8727521b58fe59ac60207474147d5))
* **release:** bump to 2.1.2 ([180826d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/180826dd8cfdcb2ad0c3f081dcd166d3cb4ff5d3))
* **release:** bump to 2.1.2 ([1312c19](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1312c190140c8e3086f97299041be7f81ea917bf))
* **release:** bump to v1.2.8 (metadata only) ([27e6a9f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/27e6a9f2a504bc687bd0bf1f30881d357cc8b80b))
* **release:** bump to v1.2.8 (metadata only) ([c7ec95d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/c7ec95d40867ba1fcf32a999312dcbc2837b7028))
* **release:** bump to v1.2.9 (metadata only) ([a3d9052](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/a3d90524db7f9e2ea4211b44021d3fbdf270ebe3))
* **release:** bump to v1.2.9 (metadata only) ([05c7570](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/05c7570ffe0ab134bf4f9c6a47ceaad640714cb5))
* **release:** bump version to 1.2.7 ([0cf7aea](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0cf7aea1c39077f76b36cfefd7339e2627a6d4f4))
* **release:** bump version to 1.2.7 ([6fd954d](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6fd954d3e6171d574b4fbc202f1d623694615d17))
* **release:** bump version to 1.3.0.1 ([555c3d3](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/555c3d36d55585fd2251d6737ac174ba47072432))
* **release:** bump version to 1.3.0.1 (no runtime changes) ([35eebea](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/35eebeabd8f44f2305828eba802646195c13ddd0))
* **release:** configure release-automation ([b07cbe7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/b07cbe728ccf49bc555cc64fe876af22249be181))
* **release:** configure release-automation ([ba2db9f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ba2db9fecb70ca188111d34474ac1cf5cb30a96c))
* **release:** finalize 1.11.4 ([2b6e066](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/2b6e06626a9b79b15dbb5748e447dc1386af449d))
* **release:** finalize 1.11.4 ([f2da99b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f2da99b8fcbb8495417cc1e8d1ef37273fbd2d4e))
* **release:** prep v1.11.5 ([59134c5](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/59134c53f751c64762c4dafe248e50cdb6752ecd))
* **release:** prep v1.11.5 ([69eaa16](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/69eaa16a5a615899dac2c27ce9f4093ab9eff47c))
* **release:** prepare 1.11.6 ([82eaacd](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/82eaacd5d9e850611548f86d1855399b9abd03dd))
* **release:** prepare 1.11.6 ([f27c0d0](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f27c0d025d47a3b79c7ff093d57f11d38da84d89))
* **release:** prepare stable v1.5.0 ([e75482f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/e75482f075e80020684f791179458470caf8a785))
* **release:** prepare v1.11.1 ([13e3deb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/13e3deb768c5d41a57c964b1276841074d2876a3))
* **release:** prepare v1.11.1 ([8a2589f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/8a2589fb11bb670f564fc90eee1a0a21a4624e78))
* **release:** prepare v1.2.13 and add release workflow ([ae6476e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ae6476e605abb42bba32d09f0bdc68f46c92f1e8))
* **release:** standardize semver configuration ([0ea7570](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0ea757038c79b0f325f1d150616f039f313fa789))
* **release:** standardize semver configuration ([af4407c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/af4407c86c04bcfeeaf0bb5bfd383d52e7efe8b0))
* **release:** v1.11.3 ([6a72f11](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6a72f112cde9374114e9cb3fe17654ebea088b1f))
* **release:** v1.2.11 ([bab2fa7](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/bab2fa754d7949689bfba17604c0e3f99cca4a8c))
* **release:** v1.2.11 ([ef32bbb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ef32bbbccb662a96e94dc48d119fb4594c305489))
* **release:** v1.2.12 ([eb88e52](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/eb88e52b0b9f275cfa1555547e5e11923e7c2393))
* **release:** v1.2.14 ([afe30b2](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/afe30b24e532b67af19aaccced379626ca6d473d))
* **release:** v1.2.14 ([f0d3b3f](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f0d3b3fd4d4ff59b6de502af4da6aca29d0f7b0a))
* **release:** v1.2.15 ([82098fb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/82098fb3ccc72d068ca8b7afd35c6f07506780f0))
* **release:** v1.2.15 ([feabde4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/feabde44daf647eded5f713617b2d7d6312c7655))
* **release:** v1.2.16 ([0cc85ec](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/0cc85ec77714121f21b3e2dedfc175778f009201))
* **release:** v1.2.16 ([227fe7b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/227fe7be8a575fe9219ba80ed5b531c48c418609))
* **release:** v1.4.0-rc.1 ([19e4acb](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/19e4acbb3bdbaf74635b70ceff58dd6157a0c78a))
* **release:** v1.4.0-rc.2 ([9fd2782](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/9fd27827069b05961ab6de5023678b8a50c660f9))
* **release:** v1.4.0-rc.2 ([927bf36](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/927bf3688184268c12b598c6a3b0e365f5f48e4f))
* **report:** regenerate QA artifacts ([684fa20](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/684fa202ef4f98d49008675b7753c2a0f3bead9f))
* **report:** update errors-first health report ([6012fa4](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6012fa414551b541b4467deeb2fe1f579c4c4977))
* **report:** update errors-first health report ([cbe3f52](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/cbe3f5215221b98065854321756123bffd6e7275))
* **strict:** drop AttendanceRepo suppressions; release 1.0.7 ([6e0e532](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6e0e532adbc8514c35de86e45cd75dda27629805))
* **strict:** drop AttendanceRepo suppressions; release 1.0.7 ([3ee1a82](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/3ee1a82d64339937346484b6b5c2785f6f91f5b0))
* **stubs:** use dev autoload for PHPStan ([61aa3db](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/61aa3db1b3776317dbeef34527574349ba5c8bde))
* switch release-please to stable 1.5.0 ([526db9b](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/526db9bc3bfc83affac3568a5a9bd571cce85310))
* **test:** centralize WP stubs ([ff60f14](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/ff60f140150125358787589eb9f63938a98c1907))
* **test:** centralize WP stubs ([47d6960](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/47d6960ad9e3cec2feb36ec69e99e75843876585))
* **tests:** drop unused DateTimeImmutable import ([6648b54](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/6648b54f420aeeaec7752f764d5532837aeef8f7))
* **tests:** drop unused DateTimeImmutable import ([1807d33](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/1807d3331698bc0622f89d8f85abbce30c8c14a0))
* **tests:** unify WP stubs and standardize phpunit config ([f399386](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/f3993861a28e8701a089e46b832ede4e446a01be))
* **tests:** unify WP stubs and standardize phpunit config ([95e218a](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/95e218a7a1ca3da922ae9cdba778eb216f1da5d0))
* **theme:** wire tokens & classes across admin/public + reliable enqueue & fallbacks ([4c7407c](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/4c7407c0d5f1b26c8cc6473f92ada59e4e51f956))
* **theme:** wire tokens & classes across admin/public + reliable enqueue & fallbacks ([354e26e](https://github.com/FlavioSerra-afk/FoodBank-Manager/commit/354e26e1068e339f0a220d670eb6e636cb815116))

## [2.2.0] — 2025-09-13
### Improved
- Live preview now writes `<style data-fbm-preview>` under `@layer fbm` and `.fbm-scope`, updating as controls change.

## [2.1.6] — 2025-09-13
### Fixed
- Theme & Design controls now apply instantly in preview and across FBM UI; corrected enqueue order & scope; hardened Settings API wiring.

## [2.1.5] — 2025-09-13
### Fixed
- Theme & Design controls now apply instantly in preview and across FBM UI; corrected enqueue order & scope; hardened Settings API wiring.

## [2.1.3] — 2025-09-13
### Added
- Typography settings (H1–H6, body, small, link/muted colors) with live preview and FBM-scoped selectors.
- Tabs design tokens (size, padding, radius, gap, indicator, states) wired to ARIA-compliant tab markup.
### Fixed
- Variables emitted only under `.fbm-scope` in `@layer fbm`; no bleed into wp-admin chrome.
### A11y
- :focus-visible outlines maintained for tabs; radios/checkboxes keep accent-color.

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
