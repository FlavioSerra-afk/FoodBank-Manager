<?php
// phpcs:ignoreFile
/**
 * Template defaults tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration\Editor;

use FoodBankManager\Registration\Editor\Conditions;
use FoodBankManager\Registration\Editor\TemplateDefaults;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Registration\Editor\TemplateDefaults
 */
final class TemplateDefaultsTest extends TestCase {
        public function test_condition_schema_version_matches_conditions(): void {
                $this->assertSame( Conditions::SCHEMA_VERSION, TemplateDefaults::condition_schema_version() );
        }

        public function test_presets_are_sanitized_and_include_groups(): void {
                $presets = TemplateDefaults::presets();

                $this->assertNotEmpty( $presets );

                foreach ( $presets as $preset ) {
                        $this->assertArrayHasKey( 'id', $preset );
                        $this->assertArrayHasKey( 'label', $preset );
                        $this->assertArrayHasKey( 'description', $preset );
                        $this->assertArrayHasKey( 'groups', $preset );

                        $this->assertNotSame( '', $preset['id'] );
                        $this->assertNotSame( '', $preset['label'] );
                        $this->assertNotEmpty( $preset['groups'] );

                        foreach ( $preset['groups'] as $group ) {
                                $this->assertArrayHasKey( 'operator', $group );
                                $this->assertArrayHasKey( 'conditions', $group );
                                $this->assertArrayHasKey( 'actions', $group );
                                $this->assertNotEmpty( $group['conditions'] );
                                $this->assertNotEmpty( $group['actions'] );
                        }

                        if ( isset( $preset['placeholders'] ) ) {
                                foreach ( $preset['placeholders'] as $placeholder ) {
                                        $this->assertArrayHasKey( 'key', $placeholder );
                                        $this->assertArrayHasKey( 'type', $placeholder );
                                        $this->assertNotSame( '', $placeholder['key'] );
                                        $this->assertContains( $placeholder['type'], array( 'field', 'value' ) );
                                }
                        }
                }
        }
}
