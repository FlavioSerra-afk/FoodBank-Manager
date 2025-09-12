<?php
declare(strict_types=1);

namespace Tests\Unit\CLI;

use FoodBankManager\CLI\Commands;
use Tests\Support\FakeIO;

final class ThrottleCommandTest extends \BaseTestCase {
    public function testShowAndSet(): void {
        \fbm_grant_caps( array( 'fb_manage_diagnostics' ) );
        update_option( 'fbm_throttle', array( 'window_seconds' => 30, 'base_limit' => 6, 'role_multipliers' => array( 'administrator' => 0 ) ) );
        $io  = new FakeIO();
        $cmd = new Commands( $io );
        $cmd->throttle_show( array(), array() );
        $this->assertSame( 'window=30 base=6', $io->lines[0] ?? '' );
        $cmd->throttle_set( array(), array( 'window' => 2, 'base' => 200, 'role' => array( 'editor:3' ) ) );
        $this->assertSame( 'window clamped to 5', $io->lines[ count( $io->lines ) - 2 ] ?? '' );
        $this->assertSame( 'base clamped to 120', $io->lines[ count( $io->lines ) - 1 ] ?? '' );
        $this->assertSame( 'Throttle updated', $io->success[0] ?? '' );
    }
}

