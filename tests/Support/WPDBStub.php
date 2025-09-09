<?php
declare(strict_types=1);

namespace FBM\Tests\Support;

final class WPDBStub
{
    public string $prefix = 'wp_';
    /** @var array<int, string> */
    public array $prepared = [];
    /** @var array<string,mixed> */
    public array $args = [];
    public ?string $last_sql = null;
    /** @var array<int, mixed> */
    public array $last_args = [];

    public function prepare(string $sql, ...$args): string
    {
        $this->prepared[] = $sql;
        $this->last_sql = $sql;
        $this->last_args = $args;
        return $sql;
    }

    public function get_results(string $sql, $output = null): array
    {
        return [];
    }

    public function get_row(string $sql, $output = null)
    {
        return null;
    }

    public function get_var(string $sql): int
    {
        return 0;
    }

    public function query(string $sql): int
    {
        return 0;
    }

    public function insert(string $table, array $data, $format = null): bool
    {
        $this->args = compact('table', 'data', 'format');
        return true;
    }
}
