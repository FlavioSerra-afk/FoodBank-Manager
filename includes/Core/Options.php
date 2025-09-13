<?php declare(strict_types=1);
// phpcs:ignoreFile

namespace FBM\Core {
    use function add_action;
    use function add_option;
    use function add_settings_error;
    use function get_option;
    use function register_setting;
    use function wp_json_encode;
    use function strlen;
    use function __;
    use function sanitize_text_field;
    use function sanitize_email;
    use function is_email;

    class Options {
        /** @return array<string,mixed> */
        public static function defaults(): array {
            return [
                'privacy' => [
                    'retention' => [
                        'applications' => ['days' => 0, 'mode' => 'keep'],
                        'attendance'   => ['days' => 0, 'mode' => 'keep'],
                        'mail'         => ['days' => 0, 'mode' => 'keep'],
                    ],
                ],
                'theme' => [
                    'admin' => [
                        'style'  => 'glass',
                        'preset' => 'light',
                        'accent' => '#0B5FFF',
                        'glass'  => [
                            'alpha'  => 0.24,
                            'blur'   => 14,
                            'elev'   => 8,
                            'radius' => 20,
                            'border' => 1,
                        ],
                    ],
                    'front' => [
                        'style'   => 'basic',
                        'preset'  => 'light',
                        'accent'  => '#0B5FFF',
                        'glass'   => [
                            'alpha'  => 0.24,
                            'blur'   => 14,
                            'elev'   => 8,
                            'radius' => 20,
                            'border' => 1,
                        ],
                        'enabled' => false,
                    ],
                    'match_front_to_admin' => false,
                    'apply_admin_chrome'   => false,
                    'apply_front_menus'    => false,
                ],
                'emails' => [
                    'from_name'    => '',
                    'from_address' => '',
                    'reply_to'     => '',
                ],
                'pdf' => [
                    'brand' => [
                        'logo'         => 0,
                        'org_name'     => '',
                        'org_address'  => '',
                        'primary_color'=> '#000000',
                        'footer_text'  => '',
                        'page_size'    => 'A4',
                        'orientation'  => 'portrait',
                    ],
                ],
            ];
        }

        /** @return array<string,mixed> */
        public static function all(): array {
            $raw = get_option('fbm_options', []);
            if (!is_array($raw)) {
                $raw = [];
            }
            $theme = get_option('fbm_theme', self::defaults()['theme']);
            if (!is_array($theme)) {
                $theme = self::defaults()['theme'];
            }
            $raw['theme'] = $theme;
            return self::merge(self::defaults(), $raw);
        }

        /**
         * Sanitize and persist settings via Settings API.
         *
         * @param array<string,mixed> $input Raw input.
         * @return array<string,mixed>
         */
        public static function sanitize_all($input): array {
            if (!is_array($input)) {
                $input = [];
            }
            $current = self::all();
            if (isset($input['theme']) && is_array($input['theme'])) {
                $json = wp_json_encode($input['theme']);
                if (is_string($json) && strlen($json) > 65536) {
                    add_settings_error('fbm_theme', 'fbm_theme', __('Theme payload too large.', 'foodbank-manager'), 'error');
                } else {
                    $san = \FoodBankManager\UI\Theme::sanitize($input['theme']);
                    update_option('fbm_theme', $san, false);
                    $current['theme'] = $san;
                }
                unset($input['theme']);
            }
            if (isset($input['emails']) && is_array($input['emails'])) {
                $emails = $current['emails'];
                $name  = sanitize_text_field((string)($input['emails']['from_name'] ?? ''));
                if (mb_strlen($name) > 200) {
                    $name = mb_substr($name, 0, 200);
                }
                $from  = sanitize_email((string)($input['emails']['from_address'] ?? ''));
                if (!is_email($from)) {
                    $from = '';
                    add_settings_error('fbm_emails', 'fbm_emails_from', __('Invalid from address.', 'foodbank-manager'), 'error');
                }
                $reply = sanitize_email((string)($input['emails']['reply_to'] ?? ''));
                if (!is_email($reply)) {
                    add_settings_error('fbm_emails', 'fbm_emails_reply', __('Invalid reply-to address.', 'foodbank-manager'), 'error');
                    $reply = '';
                }
                $emails['from_name']    = $name;
                $emails['from_address'] = $from;
                $emails['reply_to']     = $reply;
                $current['emails']      = $emails;
                unset($input['emails']);
            }
            return array_replace_recursive($current, $input);
        }

        /** @param array<string,mixed> $patch */
        public static function save(array $patch): bool {
            $current = self::all();
            if (isset($patch['theme'])) {
                update_option('fbm_theme', $patch['theme'], false);
                $current['theme'] = $patch['theme'];
                unset($patch['theme']);
            }
            $next = self::merge($current, $patch);
            unset($next['theme']);
            return update_option('fbm_options', $next, false); // @phpstan-ignore-line
        }

        /**
         * @param array<string,mixed> $a
         * @param array<string,mixed> $b
         * @return array<string,mixed>
         */
        private static function merge(array $a, array $b): array {
            foreach ($b as $k => $v) {
                if (is_array($v) && isset($a[$k]) && is_array($a[$k])) {
                    $a[$k] = self::merge($a[$k], $v);
                } else {
                    $a[$k] = $v;
                }
            }
            return $a;
        }

        /**
         * @param mixed $key
         * @return mixed
         */
        public static function get(string $section, $key = null, mixed $default = null): mixed {
            if (!is_string($key)) {
                $default = $key;
                $parts   = explode('.', $section, 2);
                $section = $parts[0];
                $key     = $parts[1] ?? '';
            }
            $all = self::all();
            return $all[$section][$key] ?? $default;
        }

        public static function boot(): void {
            add_action(
                'admin_init',
                static function (): void {
                    register_setting(
                        'fbm',
                        'fbm_options',
                        array(
                            'sanitize_callback' => '\\FBM\\Core\\Options::sanitize_all',
                        )
                    );
                    register_setting(
                        'fbm_theme',
                        'fbm_theme',
                        array(
                            'type'              => 'array',
                            'sanitize_callback' => '\\FoodBankManager\\UI\\Theme::sanitize',
                            'default'           => \FoodBankManager\UI\Theme::defaults(),
                            'show_in_rest'      => false,
                        )
                    );
                    if ( null === get_option( 'fbm_theme', null ) ) {
                        add_option( 'fbm_theme', \FoodBankManager\UI\Theme::defaults(), '', false );
                    }
                }
            );
        }

        /**
         * @param array<string,mixed>|string $section
         * @param mixed $value
         */
        public static function update($section, $value = null): bool {
            if (is_array($section) && null === $value) {
                $patch = $section;
            } elseif (is_string($section)) {
                if (str_contains($section, '.')) {
                    [$sec, $key] = explode('.', $section, 2);
                    $patch       = [$sec => [$key => $value]];
                } else {
                    $patch = [$section => $value];
                }
            } else {
                return false;
            }
            return self::save($patch);
        }

        /** @param mixed $value */
        public static function set(string $path, $value): bool {
            return self::update($path, $value);
        }

        /**
         * Configuration health summary.
         *
         * @return array{smtp:string,api:string,kek:string}
         */
        public static function config_health(): array {
            $smtp_ok = defined('FBM_SMTP_HOST') && FBM_SMTP_HOST !== ''
                && defined('FBM_SMTP_PORT') && FBM_SMTP_PORT !== ''
                && '' !== (string) self::get('emails.from_address', '');
            $api_ok  = (defined('FBM_API_KEY') && FBM_API_KEY !== '')
                || '' !== (string) self::get('api.key', '');
            $kek_ok  = defined('FBM_KEK_BASE64') && FBM_KEK_BASE64 !== '';
            return array(
                'smtp' => $smtp_ok ? 'Configured' : 'Not configured',
                'api'  => $api_ok ? 'Configured' : 'Not configured',
                'kek'  => $kek_ok ? 'Loaded from wp-config.php' : 'Not configured',
            );
        }

        /** @return array<mixed> */
        public static function get_form_presets_custom(): array {
            return [];
        }

        /** @return array<int,string> */
        public static function db_filter_allowed_keys(): array {
            return ['foo'];
        }

        /** @param array<int,array<string,mixed>> $presets */
        public static function set_db_filter_presets(array $presets): bool {
            return true;
        }

        /** @return array<int,array{name:string,query:array<string,string>}> */
        public static function get_db_filter_presets(): array {
            return array_fill(0, 21, ['name' => '', 'query' => []]);
        }

        /** @return array{subject:string,body_html:string,updated_at:string} */
        public static function get_template(string $id): array {
            $all   = $GLOBALS['fbm_templates'] ?? [];
            $tpl   = $all[$id] ?? [];
            $tpl  += ['subject' => '', 'body_html' => '', 'updated_at' => ''];
            return $tpl;
        }

        /** @param array<string,string> $data */
        public static function set_template(string $id, array $data): bool {
            $all                 = $GLOBALS['fbm_templates'] ?? [];
            $data               += ['subject' => '', 'body_html' => '', 'updated_at' => current_time('mysql')];
            $all[$id]            = $data;
            $GLOBALS['fbm_templates'] = $all;
            return true;
        }

        public static function reset_template(string $id): bool {
            $all = $GLOBALS['fbm_templates'] ?? [];
            unset($all[$id]);
            $GLOBALS['fbm_templates'] = $all;
            return true;
        }
    }
}

namespace FoodBankManager\Core {
    class Options extends \FBM\Core\Options {}
}
