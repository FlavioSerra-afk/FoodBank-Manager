<?php
declare(strict_types=1);

namespace Tests\Unit\CLI;

use FoodBankManager\CLI\Commands;
use Tests\Support\FakeIO;
use PHPUnit\Framework\TestCase;
use function fbm_test_set_wp_mail_result;

final class MailTestCommandTest extends TestCase {
    public function testSuccessAndFailure(): void {
        $io  = new FakeIO();
        $cmd = new Commands($io);
        fbm_test_set_wp_mail_result(true);
        $cmd->mail_test([], ['to' => 'user@example.com']);
        $this->assertSame(['Sent to user@example.com'], $io->success);
        fbm_test_set_wp_mail_result(false);
        $cmd->mail_test([], ['to' => 'user@example.com']);
        $this->assertSame(['Send failed'], $io->errors);
    }
}
