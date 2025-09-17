<?php
/**
 * Capability and role registration tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Auth;

use FoodBankManager\Auth\Capabilities;
use FoodBankManager\Core\Plugin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Auth\Capabilities
 * @covers \FoodBankManager\Core\Plugin::activate
 */
final class CapabilitiesTest extends TestCase {
        /**
         * Previously registered role objects.
         *
         * @var array<string,\WP_Role>|null
         */
        private ?array $previous_roles = null;

        /**
         * Previously registered users.
         *
         * @var array<int,array<string,mixed>>|null
         */
        private ?array $previous_users = null;

        protected function setUp(): void {
                parent::setUp();

                $this->previous_roles = isset( $GLOBALS['fbm_roles'] ) && is_array( $GLOBALS['fbm_roles'] )
                        ? $GLOBALS['fbm_roles']
                        : null;
                $this->previous_users = isset( $GLOBALS['fbm_users'] ) && is_array( $GLOBALS['fbm_users'] )
                        ? $GLOBALS['fbm_users']
                        : null;

                $GLOBALS['fbm_roles'] = array();
                $GLOBALS['fbm_users'] = array();

                $this->reset_capability_cache();
        }

        protected function tearDown(): void {
                if ( null === $this->previous_roles ) {
                        unset( $GLOBALS['fbm_roles'] );
                } else {
                        $GLOBALS['fbm_roles'] = $this->previous_roles;
                }

                if ( null === $this->previous_users ) {
                        unset( $GLOBALS['fbm_users'] );
                } else {
                        $GLOBALS['fbm_users'] = $this->previous_users;
                }

                $this->reset_capability_cache();

                parent::tearDown();
        }

        public function test_ensure_assigns_capabilities_to_mapped_roles(): void {
                $administrator = new \WP_Role( 'administrator', array(), 'Administrator' );
                $manager       = new \WP_Role( 'fbm_manager', array(), 'Manager' );
                $staff         = new \WP_Role( 'fbm_staff', array(), 'Staff' );

                $GLOBALS['fbm_roles'] = array(
                        'administrator' => $administrator,
                        'fbm_manager'   => $manager,
                        'fbm_staff'     => $staff,
                );

                Capabilities::ensure();

                $this->assertRoleCapabilities( $administrator, Capabilities::all() );
                $this->assertRoleCapabilities( $manager, Capabilities::bundle( 'fbm_manager' ) );
                $this->assertRoleCapabilities( $staff, Capabilities::bundle( 'fbm_staff' ) );

                $this->assertArrayNotHasKey( 'fbm_diagnostics', $manager->capabilities );
                $this->assertArrayNotHasKey( 'fbm_manage', $staff->capabilities );
                $this->assertArrayNotHasKey( 'fbm_export', $staff->capabilities );
        }

        public function test_activate_registers_roles_and_grants_capabilities(): void {
                $GLOBALS['fbm_roles']['administrator'] = new \WP_Role( 'administrator', array(), 'Administrator' );

                Plugin::activate();

                $administrator = \get_role( 'administrator' );
                $manager       = \get_role( 'fbm_manager' );
                $staff         = \get_role( 'fbm_staff' );
                $member        = \get_role( 'foodbank_member' );

                $this->assertInstanceOf( \WP_Role::class, $administrator );
                $this->assertInstanceOf( \WP_Role::class, $manager );
                $this->assertInstanceOf( \WP_Role::class, $staff );
                $this->assertInstanceOf( \WP_Role::class, $member );

                $this->assertRoleCapabilities( $administrator, Capabilities::all() );
                $this->assertRoleCapabilities( $manager, Capabilities::bundle( 'fbm_manager' ) );
                $this->assertRoleCapabilities( $staff, Capabilities::bundle( 'fbm_staff' ) );
                $this->assertRoleCapabilities( $member, Capabilities::all() );
        }

        public function test_reactivation_backfills_capabilities_without_downgrade(): void {
                $GLOBALS['fbm_roles'] = array(
                        'fbm_manager' => new \WP_Role(
                                'fbm_manager',
                                array(
                                        'fbm_manage'   => true,
                                        'legacy_extra' => true,
                                ),
                                'Legacy Manager'
                        ),
                        'fbm_staff'   => new \WP_Role(
                                'fbm_staff',
                                array(
                                        'fbm_view' => true,
                                ),
                                'Legacy Staff'
                        ),
                );

                $GLOBALS['fbm_users'][1] = array(
                        'ID'         => 1,
                        'user_login' => 'legacy_manager',
                        'roles'      => array( 'fbm_manager' ),
                );

                Plugin::activate();

                $manager = \get_role( 'fbm_manager' );
                $staff   = \get_role( 'fbm_staff' );

                $this->assertInstanceOf( \WP_Role::class, $manager );
                $this->assertInstanceOf( \WP_Role::class, $staff );

                $this->assertRoleCapabilities( $manager, Capabilities::bundle( 'fbm_manager' ) );
                $this->assertRoleCapabilities( $staff, Capabilities::bundle( 'fbm_staff' ) );
                $this->assertArrayHasKey( 'legacy_extra', $manager->capabilities );
                $this->assertTrue( $manager->capabilities['legacy_extra'] );

                $user = new \WP_User( 1, $GLOBALS['fbm_users'][1] );
                $this->assertContains( 'fbm_manager', $user->roles );
        }

        /**
         * Reset the cached ensure() state between tests.
         */
        private function reset_capability_cache(): void {
                $property = new \ReflectionProperty( Capabilities::class, 'ensured' );
                $property->setAccessible( true );
                $property->setValue( null, false );
        }

        /**
         * Assert that a role contains the expected capabilities.
         *
         * @param \WP_Role $role       Role instance to inspect.
         * @param string[] $expected    Capabilities expected to be granted.
         */
        private function assertRoleCapabilities( \WP_Role $role, array $expected ): void {
                foreach ( $expected as $capability ) {
                        $this->assertArrayHasKey( $capability, $role->capabilities );
                        $this->assertTrue( $role->capabilities[ $capability ] );
                }
        }
}
