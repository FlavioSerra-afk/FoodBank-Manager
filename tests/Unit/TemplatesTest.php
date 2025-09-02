<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Mail\Templates;

final class TemplatesTest extends TestCase {
	public function setUp(): void {
		global $fbm_test_options;
		$fbm_test_options = array();
	}

	public function testRenderReplacesTokens(): void {
		$vars     = array(
			'application_id'   => 42,
			'first_name'       => 'Alice',
			'last_name'        => 'Smith',
			'created_at'       => '2024-01-01 00:00:00',
			'summary_table'    => '<p>Summary</p>',
			'qr_code_url'      => 'https://example.com/qr.png',
			'reference'        => 'FBM-42',
			'application_link' => 'https://example.com',
		);
		$rendered = Templates::render( 'applicant_confirmation', $vars );
		$this->assertStringContainsString( 'FBM-42', $rendered['subject'] );
		$this->assertStringContainsString( 'Alice', $rendered['body_html'] );
	}
}
