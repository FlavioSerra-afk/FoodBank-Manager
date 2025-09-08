<?php declare(strict_types=1);

if (!function_exists('filter_input')) {
    function filter_input(int $type, string $var_name, int $filter = FILTER_DEFAULT, array|int $options = []) {
        if ($type === INPUT_POST) {
            return $_POST[$var_name] ?? null;
        }
        if ($type === INPUT_GET) {
            return $_GET[$var_name] ?? null;
        }
        if ($type === INPUT_SERVER) {
            return $_SERVER[$var_name] ?? null;
        }
        return null;
    }
}

if (!function_exists('filter_input_array')) {
    function filter_input_array(int $type, $definition = FILTER_DEFAULT, bool $add_empty = true) {
        if ($type === INPUT_POST) {
            return $_POST;
        }
        if ($type === INPUT_GET) {
            return $_GET;
        }
        return null;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

fbm_test_reset_globals();

