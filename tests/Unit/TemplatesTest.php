<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Mail\Templates;

final class TemplatesTest extends TestCase {
    public function testDefaultsContainTemplates(): void {
        $defaults = Templates::defaults();
        $this->assertArrayHasKey( 'applicant_confirmation', $defaults );
        $this->assertArrayHasKey( 'admin_notification', $defaults );
    }

    public function testRenderReplacesWhitelistedTokensOnly(): void {
        $vars = array(
            'first_name'       => 'Alice',
            'last_name'        => 'Smith',
            'application_id'   => '42',
            'site_name'        => 'Food Bank',
            'appointment_time' => '10:00',
            'extra'            => 'ignored',
        );
        $subject = Templates::render_subject( 'applicant_confirmation', $vars );
        $this->assertSame( 'We received your application — Ref 42', $subject );

        $body = Templates::render_body( 'admin_notification', $vars );
        $this->assertStringContainsString( 'Alice Smith', $body );
        $this->assertStringNotContainsString( 'ignored', $body );
    }

    public function testUnknownTokensLeftAsIs(): void {
        $body = Templates::render_body( 'admin_notification', array() );
        $this->assertStringContainsString( '{last_name}', $body );
    }
}

