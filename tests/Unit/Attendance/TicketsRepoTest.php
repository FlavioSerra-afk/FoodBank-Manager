<?php
declare(strict_types=1);

namespace FoodBankManager\Tests\Unit\Attendance;

use FBM\Attendance\TicketsRepo;
use PHPUnit\Framework\TestCase;

final class TicketsRepoTest extends TestCase {
    private $db;

    protected function setUp(): void {
        parent::setUp();
        $this->db = new class {
            public string $prefix = 'wp_';
            public array $rows = array();
            public int $insert_id = 0;
            public array $last_args = array();
            public function insert($table, $data, $format) { $this->insert_id++; $data['id']=$this->insert_id; $this->rows[$this->insert_id]=$data; return true; }
            public function update($table, $data, $where, $df=null, $wf=null) { $id=$where['id']; if(isset($this->rows[$id])){$this->rows[$id]=array_merge($this->rows[$id],$data); return 1;} return 0; }
            public function prepare($sql, ...$args){ $this->last_args=$args; return $sql; }
            public function get_row($sql, $output) { $id=(int)($this->last_args[0]??0); return $this->rows[$id]??null; }
            public function get_results($sql,$output){ return array_values($this->rows); }
            public function get_var($sql){ return count($this->rows); }
        };
        $GLOBALS['wpdb'] = $this->db;
    }

    public function testIssueAndList(): void {
        $id = TicketsRepo::issue(1, 'A@EXAMPLE.COM', 1000, 'nonce', 'hash');
        $this->assertSame(1, $id);
        $list = TicketsRepo::list_for_event(1);
        $this->assertSame(1, $list['total']);
        $this->assertSame('a@example.com', strtolower((string)($list['rows'][0]['recipient'] ?? '')));
    }

    public function testRegenerateAndRevoke(): void {
        $id = TicketsRepo::issue(1, 'a@example.com', 1000, 'n1', 'h1');
        $new = TicketsRepo::regenerate($id, 1100, 'n2', 'h2');
        $this->assertGreaterThan(0, $new);
        $this->assertTrue(TicketsRepo::revoke($new));
    }
}
