<?php
// phpcs:ignoreFile
/**
 * Conditions helper tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration\Editor;

use FoodBankManager\Registration\Editor\Conditions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Registration\Editor\Conditions
 */
final class ConditionsTest extends TestCase {
        public function test_sanitize_groups_strips_invalid_entries(): void {
                $raw = array(
                        array(
                                'operator'   => 'or',
                                'conditions' => array(
                                        array(
                                                'field'    => ' fbm_first_name ',
                                                'operator' => 'equals',
                                                'value'    => ' Yes ',
                                        ),
                                        array(
                                                'field'    => '',
                                                'operator' => 'equals',
                                                'value'    => '',
                                        ),
                                ),
                                'actions'    => array(
                                        array(
                                                'type'   => 'show',
                                                'target' => 'fbm_extra',
                                        ),
                                        array(
                                                'type'   => 'hide',
                                                'target' => '',
                                        ),
                                ),
                        ),
                        'invalid',
                );

                $sanitized = Conditions::sanitize_groups( $raw );

                $this->assertCount( 1, $sanitized );
                $group = $sanitized[0];

                $this->assertSame( 'or', $group['operator'] );
                $this->assertCount( 1, $group['conditions'] );
                $this->assertSame( 'fbm_first_name', $group['conditions'][0]['field'] );
                $this->assertSame( 'yes', $group['conditions'][0]['value'] );
                $this->assertCount( 1, $group['actions'] );
                $this->assertSame( 'fbm_extra', $group['actions'][0]['target'] );
        }

        public function test_preview_import_suggests_mapping_and_flags_missing(): void {
                $payload = array(
                        'schema'     => array( 'version' => Conditions::SCHEMA_VERSION ),
                        'fields'     => array(
                                array(
                                        'name'  => 'household',
                                        'label' => 'Household Size',
                                        'type'  => 'number',
                                ),
                                array(
                                        'name'  => 'phoneField',
                                        'label' => 'Contact Phone',
                                        'type'  => 'text',
                                ),
                        ),
                        'conditions' => array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'household',
                                                                'operator' => 'gt',
                                                                'value'    => '4',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'phoneField',
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                );

                $current_fields = array(
                        array(
                                'name'  => 'fbm_household_size',
                                'label' => 'Household Size',
                                'type'  => 'number',
                        ),
                        array(
                                'name'  => 'fbm_contact_phone',
                                'label' => 'Phone',
                                'type'  => 'tel',
                        ),
                );

                $preview = Conditions::preview_import( $payload, $current_fields );

                $this->assertSame( Conditions::SCHEMA_VERSION, $preview['schemaVersion'] );
                $this->assertTrue( $preview['enabled'] );
                $this->assertSame( 'fbm_household_size', $preview['fields']['suggested']['household'] );
                $this->assertSame( '', $preview['fields']['suggested']['phoneField'] );

                $this->assertSame( array( 'phoneField' ), $preview['analysis'][0]['missing'] );
        }

        public function test_apply_import_maps_groups_and_records_skipped(): void {
                $payload = array(
                        'schema'     => array( 'version' => Conditions::SCHEMA_VERSION ),
                        'conditions' => array(
                                'enabled' => true,
                                'groups'  => array(
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'household',
                                                                'operator' => 'gt',
                                                                'value'    => '4',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'phoneField',
                                                        ),
                                                ),
                                        ),
                                        array(
                                                'operator'   => 'and',
                                                'conditions' => array(
                                                        array(
                                                                'field'    => 'missingField',
                                                                'operator' => 'equals',
                                                                'value'    => 'yes',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'otherField',
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                );

                $current_fields = array(
                        array(
                                'name'  => 'fbm_household_size',
                                'label' => 'Household Size',
                                'type'  => 'number',
                        ),
                        array(
                                'name'  => 'fbm_contact_phone',
                                'label' => 'Phone',
                                'type'  => 'tel',
                        ),
                );

                $mapping = array(
                        'household'  => 'fbm_household_size',
                        'phoneField' => 'fbm_contact_phone',
                );

                $result = Conditions::apply_import( $payload, $mapping, $current_fields );

                $this->assertTrue( $result['enabled'] );
                $this->assertCount( 1, $result['groups'] );

                $group = $result['groups'][0];
                $this->assertSame( 'fbm_household_size', $group['conditions'][0]['field'] );
                $this->assertSame( 'fbm_contact_phone', $group['actions'][0]['target'] );
                $this->assertSame( 'number', $group['conditions'][0]['field_type'] );

                $this->assertCount( 1, $result['skipped'] );
                $this->assertSame( 'missing_field', $result['skipped'][0]['reason'] );
        }
}
