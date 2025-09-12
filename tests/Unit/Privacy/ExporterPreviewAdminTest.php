<?php
declare(strict_types=1);

namespace Tests\Unit\Privacy;

use FoodBankManager\Admin\DiagnosticsPrivacy;
use FBM\Privacy\Exporter;

if (!class_exists('PrivacyDbStub2')) {
    class PrivacyDbStub2 {
        public string $prefix = 'wp_';
        /** @var array<string,list<array<string,mixed>>> */
        public array $data = array();
        /** @var array<int,mixed> */
        public array $last_args = array();
        public function prepare(string $sql, ...$args): string { $this->last_args = $args; return $sql; }
        public function get_results($sql, $type = 'ARRAY_A'): array {
            $email  = $this->last_args[0] ?? '';
            $limit  = (int)($this->last_args[1] ?? 50);
            $offset = (int)($this->last_args[2] ?? 0);
            foreach ($this->data as $table => $rows) {
                if (strpos($sql, $table) !== false) {
                    $rows = array_values(array_filter($rows, fn($r) => $r['email'] === $email));
                    return array_slice($rows, $offset, $limit);
                }
            }
            return array();
        }
    }
}

final class ExporterPreviewAdminTest extends \BaseTestCase {
    protected function setUp(): void {
        parent::setUp();
        $db = new PrivacyDbStub2();
        $db->data['wp_fb_attendance'] = [
            ['id' => 1, 'email' => 'user@example.com', 'note' => 'secret'],
            ['id' => 2, 'email' => 'user@example.com', 'note' => 'again'],
        ];
        global $wpdb;
        $wpdb = $db;
        fbm_grant_manager();
    }

    public function testPreviewPaginationAndMasking(): void {
        $_POST = [
            'fbm_privacy_action' => 'fbm_privacy_preview',
            'email' => 'user@example.com',
            'page_size' => '1',
        ];
        fbm_test_set_request_nonce('fbm_privacy_preview');
        DiagnosticsPrivacy::handle_actions();
        $preview = DiagnosticsPrivacy::preview_summary();
        $this->assertFalse($preview['done']);
        $this->assertCount(1, $preview['data']);
        $fields = [];
        foreach ($preview['data'][0]['data'] as $field) {
            $fields[$field['name']] = $field['value'];
        }
        $this->assertSame('***', $fields['note']);
    }
}
