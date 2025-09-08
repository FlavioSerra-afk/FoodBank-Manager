<?php
declare(strict_types=1);

namespace {
}

namespace FoodBankManager\Tests\Unit\Forms {

use FoodBankManager\Forms\PresetsRepo;
use PHPUnit\Framework\TestCase;

final class PresetsRepoTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['fbm_options'] = array();
    }

    public function testUpsertGetDelete(): void {
        $schema = array(
            'meta'   => array( 'name' => 'Test', 'slug' => 'my form', 'captcha' => false ),
            'fields' => array(
                array( 'id' => 'first', 'type' => 'text', 'label' => 'First', 'required' => true ),
            ),
        );
        PresetsRepo::upsert( $schema );
        $list = PresetsRepo::list();
        $this->assertCount( 1, $list );
        $this->assertSame( 'myform', $list[0]['slug'] );
        $preset = PresetsRepo::get_by_slug( 'myform' );
        $this->assertSame( 'Test', $preset['meta']['name'] );
        PresetsRepo::delete( 'myform' );
        $this->assertCount( 0, PresetsRepo::list() );
    }
}
}
