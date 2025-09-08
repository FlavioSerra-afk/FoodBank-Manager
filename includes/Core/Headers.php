<?php
/**
 * Header helpers.
 *
 * @package FoodBankManager
 */

if ( ! function_exists( 'fbm_send_headers' ) ) {
    /**
     * Send an array of headers.
     *
     * @param array<int,string> $headers Header lines.
     * @return void
     */
    function fbm_send_headers( array $headers ): void {
        foreach ( $headers as $h ) {
            header( $h );
        }
    }
}
