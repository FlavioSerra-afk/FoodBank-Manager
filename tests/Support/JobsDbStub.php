<?php
namespace Tests\Support;

class JobsDbStub {
    public string $prefix = 'wp_';
    /** @var array<string,array<int,array<string,mixed>>> */
    public array $tables = array();
    /** @var array<string,int> */
    public array $insert_ids = array();
    public int $last_insert_id = 0;
    public int $insert_id = 0;
    public ?string $last_sql = null;
    /** @var array<int,mixed> */
    public array $last_args = array();

    public function prepare(string $sql, ...$args): string {
        $this->last_sql  = $sql;
        $this->last_args = $args;
        return $sql;
    }

    public function insert(string $table, array $data, $format = null): bool {
        $id = ($this->insert_ids[$table] ?? 0) + 1;
        $this->insert_ids[$table] = $id;
        $data['id'] = $id;
        $this->tables[$table][$id] = $data;
        $this->last_insert_id = $id;
        $this->insert_id      = $id;
        return true;
    }

    public function get_row(string $sql, $output = \ARRAY_A) {
        $id    = $this->last_args[0] ?? 0;
        $table = $this->extract_table($sql);
        return $this->tables[$table][$id] ?? null;
    }

    public function get_results(string $sql, $output = \ARRAY_A): array {
        $table = $this->extract_table($this->last_sql ?? '');
        $rows  = array_values($this->tables[$table] ?? array());
        if (preg_match('/ORDER BY\s+(\w+)\s+(ASC|DESC)/i', (string) $this->last_sql, $m)) {
            $key = $m[1];
            $dir = strtoupper($m[2]) === 'ASC' ? 1 : -1;
            usort($rows, fn($a,$b) => ($a[$key] <=> $b[$key]) * $dir);
        }
        $limit = $this->last_args[0] ?? count($rows);
        return array_slice($rows, 0, (int) $limit);
    }

    public function update(string $table, array $data, array $where, $format = null, $where_format = null): bool {
        $id = $where['id'] ?? 0;
        if (isset($this->tables[$table][$id])) {
            $this->tables[$table][$id] = array_merge($this->tables[$table][$id], $data);
            return true;
        }
        return false;
    }

    public function query(string $sql): int {
        $table = $this->prefix . 'fbm_jobs';
        if (str_contains($sql, $table) && str_contains($sql, "status='running'")) {
            foreach ($this->tables[$table] ?? array() as $id => &$row) {
                if ($row['status'] === 'pending') {
                    $row['status'] = 'running';
                    $this->last_insert_id = $id;
                    return 1;
                }
            }
            return 0;
        }
        if (str_contains($sql, 'attempts = attempts + 1')) {
            $id = $this->last_args[0] ?? 0;
            if (isset($this->tables[$table][$id])) {
                $this->tables[$table][$id]['attempts']++;
                return 1;
            }
        }
        return 0;
    }

    public function get_var(string $sql): int {
        if (str_contains($sql, 'LAST_INSERT_ID')) {
            return $this->last_insert_id;
        }
        return 0;
    }

    private function extract_table(string $sql): string {
        if (preg_match('/FROM\s+(\w+)/i', $sql, $m)) {
            return $m[1];
        }
        return $this->prefix . 'fbm_jobs';
    }
}
