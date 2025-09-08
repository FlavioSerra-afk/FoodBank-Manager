<?php
declare(strict_types=1);

namespace FoodBankManager\Tests\Unit\Forms {

use FoodBankManager\Forms\Schema;
use PHPUnit\Framework\TestCase;

final class SchemaTest extends TestCase {
    public function testNormalizeValid(): void {
        $schema = array(
            'meta'   => array( 'name' => ' Test ', 'slug' => 'My Form', 'captcha' => true ),
            'fields' => array(
                array( 'id' => 'first', 'type' => 'text', 'label' => 'First', 'required' => true ),
                array( 'id' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => false ),
            ),
        );
        $normalized = Schema::normalize( $schema );
        $this->assertSame( 'myform', $normalized['meta']['slug'] );
        $this->assertTrue( $normalized['meta']['captcha'] );
        $this->assertCount( 2, $normalized['fields'] );
    }

    public function testInvalidTypeThrows(): void {
        $this->expectException( \InvalidArgumentException::class );
        Schema::normalize(
            array(
                'meta'   => array( 'name' => 'X', 'slug' => 'x' ),
                'fields' => array(
                    array( 'id' => 'a', 'type' => 'invalid', 'label' => 'A' ),
                ),
            )
        );
    }

    public function testDuplicateIdThrows(): void {
        $this->expectException( \InvalidArgumentException::class );
        Schema::normalize(
            array(
                'meta'   => array( 'name' => 'X', 'slug' => 'x' ),
                'fields' => array(
                    array( 'id' => 'a', 'type' => 'text', 'label' => 'A' ),
                    array( 'id' => 'a', 'type' => 'text', 'label' => 'B' ),
                ),
            )
        );
    }

    public function testConsentAndFileFieldsNormalized(): void {
        $schema = array(
            'meta'   => array( 'name' => 'T', 'slug' => 't' ),
            'fields' => array(
                array( 'id' => 'agree', 'type' => 'consent', 'label' => 'Yes', 'required' => true ),
                array( 'id' => 'upload', 'type' => 'file', 'label' => 'Doc', 'required' => false ),
            ),
        );
        $normalized = Schema::normalize( $schema );
        $this->assertSame( 'agree', $normalized['fields'][0]['id'] );
        $this->assertSame( 'file', $normalized['fields'][1]['type'] );
    }
}
}
