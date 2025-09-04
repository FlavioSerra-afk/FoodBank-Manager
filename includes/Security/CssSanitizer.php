<?php // phpcs:ignoreFile
/**
 * Sanitize custom CSS snippets.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Security;

/**
 * Custom CSS sanitizer.
 */
final class CssSanitizer {
    /**
     * Sanitize a CSS string.
     *
     * @param string $css Raw CSS.
     * @return string Sanitized CSS (may be empty).
     */
    public static function sanitize( string $css ): string {
        $css = substr( $css, 0, 8192 );
        $css = str_ireplace(
            array( '@import', '@font-face', '@keyframes', 'url(', 'expression(', '<', '>' ),
            '',
            $css
        );

        $out      = array();
        $allowed  = array(
            'color',
            'background-color',
            'border-color',
            'font-family',
            'font-size',
            'line-height',
            'margin',
            'padding',
            'gap',
            'border-radius',
        );

        if ( preg_match_all( '/([^{}]+)\{([^}]*)\}/', $css, $blocks, PREG_SET_ORDER ) ) {
            foreach ( $blocks as $block ) {
                $selector = trim( $block[1] );
                $body     = array();
                foreach ( explode( ';', (string) $block[2] ) as $decl ) {
                    $parts = explode( ':', $decl, 2 );
                    if ( count( $parts ) !== 2 ) {
                        continue;
                    }
                    $prop = strtolower( trim( $parts[0] ) );
                    $val  = trim( $parts[1] );
                    if ( ! in_array( $prop, $allowed, true ) ) {
                        continue;
                    }
                    $val    = preg_replace( '/!\s*important/i', '', $val );
                    $body[] = $prop . ':' . $val;
                }
                if ( $body ) {
                    $out[] = $selector . '{' . implode( ';', $body ) . ';}';
                }
            }
        }

        $clean = implode( '', $out );
        if ( strlen( $clean ) > 8192 ) {
            $clean = substr( $clean, 0, 8192 );
        }
        return $clean;
    }
}
