<?php
namespace Tests\Support;

class EventsDbStub {
    public string $prefix = 'wp_';
    public array $rows = [];
    public int $insert_id = 0;
    public array $prepared = [];
    public array $args_history = [];
    public array $last_args = [];
    public function prepare(string $sql, ...$args): string {
        $this->prepared[] = $sql;
        $this->args_history[] = $args;
        $this->last_args = $args;
        return $sql;
    }
    public function insert(string $table, array $data, $format = null): bool {
        $this->insert_id = count($this->rows) + 1;
        $data['id'] = $this->insert_id;
        $this->rows[$this->insert_id] = $data;
        return true;
    }
    public function update(string $table, array $data, array $where, $format = null, $where_format = null): bool {
        $id = $where['id'] ?? 0;
        if (isset($this->rows[$id])) {
            $this->rows[$id] = array_merge($this->rows[$id], $data);
            return true;
        }
        return false;
    }
    public function delete(string $table, array $where, array $where_format): bool {
        $id = $where['id'] ?? 0;
        if (isset($this->rows[$id])) {
            unset($this->rows[$id]);
            return true;
        }
        return false;
    }
    public function get_row(string $query, $output = \ARRAY_A) {
        $id = $this->last_args[0] ?? 0;
        return $this->rows[$id] ?? null;
    }
    public function get_results(string $query, $output = \ARRAY_A): array {
        return array_values($this->rows);
    }
    public function get_var(string $query): int {
        return count($this->rows);
    }
}
