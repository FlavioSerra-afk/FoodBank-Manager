<?php
declare(strict_types=1);

namespace FBM\Attendance {
    class TicketsRepo {
        public static array $rows = array();
        public static function issue(int $event_id, string $recipient, int $exp, string $nonce, string $hash): int {
            $id = count(self::$rows) + 1;
            self::$rows[$id] = array('id'=>$id,'event_id'=>$event_id,'recipient'=>$recipient,'exp'=>gmdate('Y-m-d H:i:s',$exp),'nonce'=>$nonce,'token_hash'=>$hash,'status'=>'active');
            return $id;
        }
        public static function get(int $id): ?array { return self::$rows[$id] ?? null; }
        public static function regenerate(int $id, int $exp, string $nonce, string $hash): int {
            self::$rows[$id]['status'] = 'revoked';
            return self::issue(self::$rows[$id]['event_id'], self::$rows[$id]['recipient'], $exp, $nonce, $hash);
        }
        public static function revoke(int $id): bool { if(isset(self::$rows[$id])){ self::$rows[$id]['status']='revoked'; return true;} return false; }
        public static function list_for_event(int $e): array { return array('rows'=>array_values(self::$rows),'total'=>count(self::$rows)); }
    }
}
namespace FBM\Mail\Templates { class TicketTemplate { public static function render(string $title,string $url): array { return array('subject'=>'s','body'=>'b'); } } }
namespace FBM\Mail { if (!class_exists('FBM\\Mail\\LogRepo')) { class LogRepo { public static array $appended=array(); public static function append(array $row): bool { self::$appended[]=$row; return true; } } } }
namespace FBM\Logging { if (!class_exists('FBM\\Logging\\Audit')) { class Audit { public static array $logs=array(); public static function log($a,$t,$id,$actor,$details=array()){ self::$logs[]=$a; } } } }

namespace FoodBankManager\Tests\Unit\Http {
use FBM\Http\TicketsController;
use FBM\Attendance\TicketService;
use FBM\Mail\LogRepo;
use FBM\Logging\Audit;
use Tests\Support\Rbac;
use Tests\Support\Exceptions\FbmDieException;

final class TicketsControllerTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        Rbac::grantManager();
        fbm_seed_nonce('unit');
        $GLOBALS['wpdb'] = new class {
            public string $prefix = 'wp_';
            public function prepare($sql, ...$args) { return $sql; }
            public function get_row($sql, $output) { return array('id'=>1,'title'=>'Event','starts_at'=>'2024-01-01 00:00:00'); }
            public function insert($table, $data) { return true; }
        };
        if (!defined('FBM_KEK_BASE64')) { define('FBM_KEK_BASE64', base64_encode(str_repeat('k',32))); }
        
        \FBM\Attendance\TicketsRepo::$rows = array();
        LogRepo::$appended = array();
        Audit::$logs = array();
    }

    public function testIssue(): void {
        $_POST = array(
            'event_id' => 1,
            'recipient' => 'a@example.com',
            '_wpnonce' => fbm_nonce('fbm_tickets_issue'),
        );
        $this->expectException(FbmDieException::class);
        TicketsController::issue();
        $this->assertSame('ticket_issue', Audit::$logs[0] ?? '');
    }

    public function testSend(): void {
        $id = \FBM\Attendance\TicketsRepo::issue(1,'a@example.com',1000,'n','h');
        $_POST = array(
            'id' => $id,
            '_wpnonce' => fbm_nonce('fbm_tickets_send'),
        );
        $this->expectException(FbmDieException::class);
        TicketsController::send();
        $this->assertSame('ticket_send', LogRepo::$appended[0]['type'] ?? '');
    }

    public function testRegenAndRevoke(): void {
        $id = \FBM\Attendance\TicketsRepo::issue(1,'a@example.com',1000,'n','h');
        $_POST = array('id'=>$id,'_wpnonce'=>fbm_nonce('fbm_tickets_regen'));
        $this->expectException(FbmDieException::class);
        TicketsController::regenerate();
        $this->assertSame('ticket_regen', Audit::$logs[0] ?? '');
        $_POST = array('id'=>$id,'_wpnonce'=>fbm_nonce('fbm_tickets_revoke'));
        $this->expectException(FbmDieException::class);
        TicketsController::revoke();
        $this->assertSame('ticket_revoke', Audit::$logs[1] ?? '');
    }

    public function testDenied(): void {
        Rbac::revokeAll();
        $_POST = array('event_id'=>1,'recipient'=>'a@example.com','_wpnonce'=>fbm_nonce('fbm_tickets_issue'));
        $this->expectException(FbmDieException::class);
        TicketsController::issue();
        $this->assertSame(array(), LogRepo::$appended);
    }
}
}
