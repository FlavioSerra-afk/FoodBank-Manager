<?php
/**
 * Tag parser tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Registration;

use FoodBankManager\Registration\Editor\TagParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \FoodBankManager\Registration\Editor\TagParser
 */
final class TagParserTest extends TestCase {
        public function test_required_field_with_attributes_is_parsed_correctly(): void {
                $template = '[text* first-name placeholder "Enter" class:primary id:custom-id autocomplete "given-name"]';

                $parser = new TagParser();
                $result = $parser->parse( $template );

                $this->assertArrayHasKey( 'first-name', $result['fields'] );
                $field = $result['fields']['first-name'];

                $this->assertTrue( $field['required'] );
                $this->assertSame( 'first-name', $field['name'] );
                $this->assertSame( 'custom-id', $field['id'] );
                $this->assertSame( array( 'primary' ), $field['classes'] );
                $this->assertSame( 'Enter', $field['placeholder'] );
                $this->assertSame( 'given-name', $field['autocomplete'] );
        }

        public function test_group_field_collects_options_and_range(): void {
                $template = '[number age-field min:1 max:10 step:1][checkbox consent use_label_element "Yes|Agree" "No|Decline"]';

                $parser = new TagParser();
                $result = $parser->parse( $template );

                $this->assertArrayHasKey( 'age-field', $result['fields'] );
                $this->assertSame(
                        array( 'min' => '1', 'max' => '10', 'step' => '1' ),
                        $result['fields']['age-field']['range']
                );

                $this->assertArrayHasKey( 'consent', $result['fields'] );
                $options = $result['fields']['consent']['options'];
                $this->assertCount( 2, $options );
                $this->assertSame( 'yes', $options[0]['value'] );
                $this->assertSame( 'Agree', $options[0]['label'] );
        }
}
