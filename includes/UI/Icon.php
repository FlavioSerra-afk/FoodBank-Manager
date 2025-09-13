<?php
// phpcs:ignoreFile
/**
 * SVG icon renderer.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\UI;

use function esc_attr;
use function esc_url;
use function sanitize_key;
use function sprintf;

final class Icon {
    /**
     * Render an SVG icon from the sprite.
     *
     * @param string               $name  Icon name.
     * @param array<string,string> $attrs Additional attributes.
     */
    public static function render( string $name, array $attrs = array() ): string {
        $id   = 'fbm-' . sanitize_key( $name );
        $href = esc_url( FBM_URL . 'assets/icons/fbm-icons.svg#' . $id );
        $aria = isset( $attrs['aria-label'] ) ? ' role="img"' : ' aria-hidden="true"';
        $out  = '<svg' . $aria;
        foreach ( $attrs as $k => $v ) {
            $out .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
        }
        $out .= '><use href="' . $href . '"></use></svg>';
        return $out;
    }
}
