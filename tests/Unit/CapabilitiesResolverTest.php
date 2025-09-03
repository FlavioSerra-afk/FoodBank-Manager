<?php
namespace FoodBankManager\Tests\Unit {

	use FoodBankManager\Auth\Capabilities;
	use FoodBankManager\Auth\CapabilitiesResolver;
	use PHPUnit\Framework\TestCase;

	class CapabilitiesResolverTest extends TestCase {
		/** @var array<int,array<string,bool>> */
		public static array $meta = array();

		protected function setUp(): void {
			self::$meta = array();
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
                        self::$meta[2] = array(
                                'fb_manage_dashboard' => true,
                                'fb_manage_forms'     => false,
                        );
                        $user        = new \WP_User();
                        $user->ID    = 2;
                        $user->roles = array( 'subscriber' );
                        $resolved    = CapabilitiesResolver::applyUserOverrides( array( 'fb_manage_database' => true ), array(), array(), $user );
                        $this->assertTrue( $resolved['fb_manage_dashboard'] );
                        $this->assertFalse( $resolved['fb_manage_forms'] );
                        $this->assertTrue( $resolved['fb_manage_database'] );
                }

                public function testUnknownCapsIgnored(): void {
                        self::$meta[3] = array( 'unknown_cap' => true );
                        $user          = new \WP_User();
                        $user->ID      = 3;
                        $user->roles   = array();
                        $resolved      = CapabilitiesResolver::applyUserOverrides( array(), array(), array(), $user );
                        $this->assertArrayNotHasKey( 'unknown_cap', $resolved );
                }
	}
}

namespace {
	class WP_User {
		public int $ID;
		/** @var string[] */
		public array $roles = array();
	}

	function get_user_meta( $user_id, $key, $single ) {
		return \FoodBankManager\Tests\Unit\CapabilitiesResolverTest::$meta[ $user_id ] ?? array();
	}
}
