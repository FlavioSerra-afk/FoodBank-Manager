<?php
/**
 * Registration editor controller diff tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Rest;

use FoodBankManager\Registration\Editor\Conditions;
use FoodBankManager\Rest\RegistrationEditorController;
use PHPUnit\Framework\TestCase;
use WP_Error;
use WP_REST_Request;

/**
 * @covers \FoodBankManager\Rest\RegistrationEditorController::handle_conditions_diff
 */
final class RegistrationEditorControllerTest extends TestCase {
        protected function setUp(): void {
                parent::setUp();

                $GLOBALS['fbm_options']['fbm_registration_template'] = '[text* fbm_household_size][email* fbm_contact_email][checkbox fbm_terms]';
        }

        public function test_handle_conditions_diff_with_full_mapping_returns_import_list(): void {
                $original = array(
                        'schema'     => array( 'version' => Conditions::SCHEMA_VERSION ),
                        'fields'     => array(
                                'incoming' => array(
                                        array(
                                                'name'  => 'household',
                                                'label' => 'Household',
                                                'type'  => 'number',
                                        ),
                                        array(
                                                'name'  => 'emailField',
                                                'label' => 'Email',
                                                'type'  => 'email',
                                        ),
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
                                                                'value'    => '3',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'emailField',
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                );

                $request = new WP_REST_Request();
                $request->set_param( 'original', $original );
                $request->set_param( 'mapping', array(
                        'household'  => 'fbm_household_size',
                        'emailField' => 'fbm_contact_email',
                ) );

                $response = RegistrationEditorController::handle_conditions_diff( $request );

                $this->assertNotInstanceOf( WP_Error::class, $response );

                $data = $response->get_data();
                $this->assertSame( Conditions::SCHEMA_VERSION, $data['schemaVersion'] );
                $this->assertTrue( $data['enabled']['incoming'] );
                $this->assertTrue( $data['enabled']['resolved'] );
                $this->assertCount( 1, $data['summary']['import'] );
                $this->assertSame( 'import', $data['diff'][0]['status'] );
                $this->assertSame( 'fbm_contact_email', $data['diff'][0]['resolved']['actions'][0]['target'] );
                $this->assertEmpty( $data['summary']['skip'] );
        }

        public function test_handle_conditions_diff_with_missing_mapping_records_skip(): void {
                $original = array(
                        'schema'     => array( 'version' => Conditions::SCHEMA_VERSION ),
                        'fields'     => array(
                                'incoming' => array(
                                        array(
                                                'name'  => 'household',
                                                'label' => 'Household',
                                                'type'  => 'number',
                                        ),
                                        array(
                                                'name'  => 'emailField',
                                                'label' => 'Email',
                                                'type'  => 'email',
                                        ),
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
                                                                'value'    => '3',
                                                        ),
                                                ),
                                                'actions'    => array(
                                                        array(
                                                                'type'   => 'show',
                                                                'target' => 'emailField',
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                );

                $request = new WP_REST_Request();
                $request->set_param( 'original', $original );
                $request->set_param( 'mapping', array(
                        'household' => 'fbm_household_size',
                ) );

                $response = RegistrationEditorController::handle_conditions_diff( $request );

                $this->assertNotInstanceOf( WP_Error::class, $response );

                $data = $response->get_data();
                $this->assertCount( 0, $data['summary']['import'] );
                $this->assertCount( 1, $data['summary']['skip'] );
                $this->assertSame( 'missing_field', $data['summary']['skip'][0]['reason'] );
                $this->assertSame( array( 'emailField' ), $data['summary']['skip'][0]['missing'] );
                $this->assertSame( 'skip', $data['diff'][0]['status'] );
        }

        public function test_handle_conditions_diff_rejects_schema_mismatch(): void {
                $original = array(
                        'schema' => array( 'version' => 999 ),
                        'fields' => array(),
                );

                $request = new WP_REST_Request();
                $request->set_param( 'original', $original );
                $request->set_param( 'mapping', array() );

                $response = RegistrationEditorController::handle_conditions_diff( $request );

                $this->assertInstanceOf( WP_Error::class, $response );
                $this->assertSame( 'fbm_invalid_schema', $response->get_error_code() );
        }
}
