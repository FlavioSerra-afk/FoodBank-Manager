<?php
declare(strict_types=1);

namespace Tests\Unit\Privacy;

use FBM\Privacy\Exporter;
use PHPUnit\Framework\TestCase;

if (!class_exists('PrivacyDBStub')) {
    class PrivacyDBStub {
        public string $prefix = 'wp_';
        /** @var array<string,list<array<string,mixed>>> */
        public array $data = [];
        /** @var array<int,mixed> */
        public array $last_args = [];
        public function prepare(string $sql, ...$args): string { $this->last_args = $args; return $sql; }
        public function get_results($sql, $type = 'ARRAY_A'): array {
            $email = $this->last_args[0] ?? '';
            $limit = (int)($this->last_args[1] ?? 50);
            $offset = (int)($this->last_args[2] ?? 0);
            foreach ($this->data as $table => $rows) {
                if (strpos($sql, $table) !== false) {
                    $rows = array_values(array_filter($rows, fn($r) => $r['email'] === $email));
                    return array_slice($rows, $offset, $limit);
                }
            }
            return [];
        }
    }
}

final class ExporterPreviewTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        global $wpdb;
        $wpdb = new PrivacyDBStub();
        $wpdb->data = [
            'wp_fb_attendance' => [
                ['id' => 1, 'email' => 'user@example.com', 'note' => 'secret'],
                ['id' => 2, 'email' => 'user@example.com', 'note' => 'second'],
            ],
        ];
    }

    public function testPaginationAndMasking(): void {
        $page1 = Exporter::export('user@example.com', 1, 1);
        $this->assertFalse($page1['done']);
        $this->assertSame('***', $page1['data'][0]['data'][0]['value']);
        $page2 = Exporter::export('user@example.com', 2, 1);
        $this->assertFalse($page2['done']);
        $page3 = Exporter::export('user@example.com', 3, 1);
        $this->assertTrue($page3['done']);
    }
}
