<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FoodBankManager\Security\CssSanitizer;

final class CssSanitizerTest extends TestCase {
        public function testStripsForbidden(): void {
                $raw   = "@import url(x);@font-face{}@keyframes x{}body{color:red!important;position:absolute;url(x);}";
                $clean = CssSanitizer::sanitize( $raw );
                $this->assertSame( 'body{color:red;}', $clean );
        }
}
