<?php
declare(strict_types=1);

use FBM\Core\Options;

final class SettingsHealthTest extends \BaseTestCase {
    public function test_health_statuses(): void {
        update_option('fbm_options', Options::defaults());
        $h = Options::config_health();
        $this->assertSame('Not configured', $h['smtp']);
        $this->assertSame('Not configured', $h['api']);
        $expected_kek = defined('FBM_KEK_BASE64') && FBM_KEK_BASE64 !== '' ? 'Loaded from wp-config.php' : 'Not configured';
        $this->assertSame($expected_kek, $h['kek']);

        if (!defined('FBM_SMTP_HOST')) {
            define('FBM_SMTP_HOST', 'smtp.example.com');
        }
        if (!defined('FBM_SMTP_PORT')) {
            define('FBM_SMTP_PORT', '587');
        }
        Options::set('emails.from_address', 'from@example.com');
        if (!defined('FBM_API_KEY')) {
            define('FBM_API_KEY', 'sekret');
        }
        if (!defined('FBM_KEK_BASE64')) {
            define('FBM_KEK_BASE64', 'abc');
        }
        $h2 = Options::config_health();
        $this->assertSame('Configured', $h2['api']);
        $this->assertSame('Loaded from wp-config.php', $h2['kek']);
    }
}
