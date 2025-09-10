<?php
declare(strict_types=1);

namespace Tests\Unit\Admin;

use FoodBankManager\Admin\UsersMeta;

final class UsersMetaColumnsTest extends \BaseTestCase {
    public function testSaveAndLoadDropsUnknown(): void {
        $user = 42;
        UsersMeta::set_db_columns( $user, array( 'id', 'email', 'unknown' ) );
        $out = UsersMeta::get_db_columns( $user );
        $this->assertContains( 'id', $out );
        $this->assertContains( 'email', $out );
        $this->assertNotContains( 'unknown', $out );
    }
}
