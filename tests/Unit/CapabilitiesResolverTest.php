<?php
namespace FoodBankManager\Tests\Unit {

        use FBM\Auth\Capabilities;
        use FoodBankManager\Auth\CapabilitiesResolver;
	use PHPUnit\Framework\TestCase;

        class CapabilitiesResolverTest extends TestCase {

                protected function setUp(): void {
                        \fbm_test_reset_globals();
                }

                public function testGrantAdmins(): void {
                        $user        = new \WP_User();
                        $user->ID    = 1;
                        $user->roles = array( 'administrator' );
                        $resolved    = CapabilitiesResolver::grantAdmins( array(), array(), array(), $user );
                        foreach ( Capabilities::all() as $cap ) {
                                $this->assertArrayHasKey( $cap, $resolved );
                                $this->assertTrue( $resolved[ $cap ] );
                        }
                }

                public function testUserOverridesApply(): void {
                        $GLOBALS['fbm_user_meta'][2]['fb_manage_dashboard'][0] = true;
                        $GLOBALS['fbm_user_meta'][2]['fb_manage_forms'][0]     = false;
                        $user        = new \WP_User();
                        $user->ID    = 2;
                        $user->roles = array( 'subscriber' );
                        $resolved    = CapabilitiesResolver::applyUserOverrides( array( 'fb_manage_database' => true ), array(), array(), $user );
                        $this->assertTrue( $resolved['fb_manage_dashboard'] );
                        $this->assertFalse( $resolved['fb_manage_forms'] );
                        $this->assertTrue( $resolved['fb_manage_database'] );
                }

                public function testUnknownCapsIgnored(): void {
                        $GLOBALS['fbm_user_meta'][3]['unknown_cap'][0] = true;
                        $user          = new \WP_User();
                        $user->ID      = 3;
                        $user->roles   = array();
                        $resolved      = CapabilitiesResolver::applyUserOverrides( array(), array(), array(), $user );
                        $this->assertArrayNotHasKey( 'unknown_cap', $resolved );
                }
        }
}

namespace {
        if ( ! class_exists( 'WP_User' ) ) {
                class WP_User {
                        public int $ID;
                        /** @var string[] */
                        public array $roles = array();
                }
        }
}
