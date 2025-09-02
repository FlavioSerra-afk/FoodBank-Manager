<?php
namespace FoodBankManager\Tests\Unit {

use FoodBankManager\Auth\Capabilities;
use FoodBankManager\Auth\CapabilitiesResolver;
use PHPUnit\Framework\TestCase;

class CapabilitiesResolverTest extends TestCase {
    /** @var array<int,array<string,bool>> */
    public static array $meta = [];

    protected function setUp(): void {
        self::$meta = [];
    }

    public function testAdministratorHasAllCaps(): void {
        $user       = new \WP_User();
        $user->ID   = 1;
        $user->roles = array( 'administrator' );
        $resolved   = CapabilitiesResolver::applyUserOverrides( array(), array(), array(), $user );
        foreach ( Capabilities::all() as $cap ) {
            $this->assertArrayHasKey( $cap, $resolved );
            $this->assertTrue( $resolved[ $cap ] );
        }
    }

    public function testUserOverridesApply(): void {
        self::$meta[2] = array( 'fb_read_entries' => true, 'fb_export_entries' => false );
        $user       = new \WP_User();
        $user->ID   = 2;
        $user->roles = array( 'subscriber' );
        $resolved   = CapabilitiesResolver::applyUserOverrides( array( 'fb_edit_entries' => true ), array(), array(), $user );
        $this->assertTrue( $resolved['fb_read_entries'] );
        $this->assertFalse( $resolved['fb_export_entries'] );
        $this->assertTrue( $resolved['fb_edit_entries'] );
    }

    public function testUnknownCapsIgnored(): void {
        self::$meta[3] = array( 'unknown_cap' => true );
        $user       = new \WP_User();
        $user->ID   = 3;
        $user->roles = array();
        $resolved   = CapabilitiesResolver::applyUserOverrides( array(), array(), array(), $user );
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
