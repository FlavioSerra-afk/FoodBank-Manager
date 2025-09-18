<?php // phpcs:ignoreFile
/**
 * Diagnostics admin page tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace {
if ( ! function_exists( 'wp_salt' ) ) {
function wp_salt( string $scheme = '' ): string { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
$salts = $GLOBALS['fbm_test_salts'] ?? array();

if ( isset( $salts[ $scheme ] ) ) {
return (string) $salts[ $scheme ];
}

return 'put your unique phrase here';
}
}
}

namespace FBM\Tests\Admin;

use FoodBankManager\Admin\DiagnosticsPage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \FoodBankManager\Admin\DiagnosticsPage
 */
final class DiagnosticsPageTest extends TestCase {
protected function setUp(): void {
parent::setUp();

$GLOBALS['fbm_options']       = array();
$GLOBALS['fbm_last_redirect'] = null;
$GLOBALS['fbm_current_caps']['fbm_manage'] = false;
$GLOBALS['fbm_test_salts'] = array(
'fbm-token-sign'  => 'secure-signing-key',
'fbm-token-store' => 'secure-storage-key',
);

$_GET     = array();
$_POST    = array();
$_REQUEST = array();
$_SERVER['REQUEST_METHOD'] = 'GET';

update_option( 'fbm_mail_failures', array() );
}

protected function tearDown(): void {
$_GET     = array();
$_POST    = array();
$_REQUEST = array();
unset( $_SERVER['REQUEST_METHOD'] );

$GLOBALS['fbm_last_redirect'] = null;
$GLOBALS['fbm_current_caps']['fbm_manage'] = false;
$GLOBALS['fbm_test_salts']    = array();

parent::tearDown();
}

/**
 * Diagnostics page should surface healthy badges when configuration is complete.
 */
public function test_render_displays_healthy_badges(): void {
$GLOBALS['fbm_current_caps']['fbm_manage'] = true;

update_option(
'fbm_settings',
array(
'mail' => array(
'transport' => 'smtp',
'username'  => 'mailer',
'password'  => 'secret-value',
),
)
);

ob_start();
DiagnosticsPage::render();
$output = ob_get_clean();

$this->assertIsString( $output );
$this->assertStringContainsString( 'fbm-status-panel', $output );
$this->assertStringContainsString( 'fbm-status-badge--healthy', $output );
$this->assertStringContainsString( 'External mail credentials are configured.', $output );
$this->assertStringContainsString( 'Custom salts are configured for token signing.', $output );
}

/**
 * Missing credentials or salts should downgrade the status badges.
 */
public function test_render_shows_degraded_badges(): void {
$GLOBALS['fbm_current_caps']['fbm_manage'] = true;

$GLOBALS['fbm_test_salts'] = array(
'fbm-token-sign'  => 'put your unique phrase here',
'fbm-token-store' => 'put your unique phrase here',
);

update_option(
'fbm_settings',
array(
'mail' => array(
'transport' => 'smtp',
'username'  => '',
'password'  => '',
),
)
);

ob_start();
DiagnosticsPage::render();
$output = ob_get_clean();

$this->assertIsString( $output );
$this->assertStringContainsString( 'fbm-status-badge--degraded', $output );
$this->assertStringContainsString( 'External mail transport is configured but credentials are incomplete.', $output );
$this->assertStringContainsString( 'Token salts should be updated in wp-config.php.', $output );
}

/**
 * Rendering should be limited to users with the diagnostics capability.
 */
public function test_render_requires_capability(): void {
$this->expectException( RuntimeException::class );
$this->expectExceptionMessage( 'You do not have permission to access this page.' );

DiagnosticsPage::render();
}

/**
 * Resend actions must validate the nonce before processing.
 */
public function test_handle_actions_rejects_invalid_nonce(): void {
$GLOBALS['fbm_current_caps']['fbm_manage'] = true;

$_GET = array(
'page'             => 'fbm-diagnostics',
'fbm_diag_action'  => 'resend',
'fbm_diag_entry'   => 'entry-1',
'_wpnonce'         => 'invalid',
);

$_REQUEST = $_GET;

$this->expectException( RuntimeException::class );
$this->expectExceptionMessage( 'invalid_nonce' );

DiagnosticsPage::handle_actions();
}
}
