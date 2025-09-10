<?php declare(strict_types=1);
// phpcs:ignoreFile

namespace FBM\Core {
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
                        'accent' => '#3B82F6',
                        'glass'  => [
                            'alpha'  => 0.10,
                            'blur'   => 12,
                            'elev'   => 8,
                            'radius' => 12,
                            'border' => 1,
                        ],
                    ],
                    'front' => [
                        'style'   => 'basic',
                        'preset'  => 'light',
                        'accent'  => '#3B82F6',
                        'glass'   => [
                            'alpha'  => 0.10,
                            'blur'   => 12,
                            'elev'   => 8,
                            'radius' => 12,
                            'border' => 1,
                        ],
                        'enabled' => false,
                    ],
                    'match_front_to_admin' => false,
                ],
                'emails' => [
                    'from_name'    => '',
                    'from_address' => '',
                    'reply_to'     => '',
                ],
            ];
        }

        /** @return array<string,mixed> */
        public static function all(): array {
            $raw = get_option('fbm_options', []);
            if (!is_array($raw)) {
                $raw = [];
            }
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
            $raw   = self::all();
            $raw   = array_replace_recursive($raw, $input);
            $theme = \FoodBankManager\UI\Theme::sanitize($raw['theme'] ?? []);
            self::update(['theme' => $theme]);
            $raw['theme'] = $theme;
            return $raw;
        }

        /** @param array<string,mixed> $patch */
        public static function save(array $patch): bool {
            $current = self::all();
            $next    = self::merge($current, $patch);
            return update_option('fbm_options', $next);
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

        public static function boot(): void {}

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
