<?php
/**
 * Columns provider for the admin database list.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Database;

use FoodBankManager\Security\Crypto;
use FoodBankManager\Security\Helpers;

/**
 * Provides column definitions for admin tables and exports.
 */
final class Columns {
    /**
     * Get column definitions for the admin list.
     *
     * Each definition contains:
     * - label   => string
     * - value   => callable(array $row): string (unescaped)
     * - sortable=> optional string sort key
     *
     * @param bool $unmask Whether to reveal sensitive data.
     * @return array<string,array<string,mixed>>
     */
    public static function for_admin_list( bool $unmask ): array {
        return array(
            'id' => array(
                'label'    => __( 'ID', 'foodbank-manager' ),
                'value'    => static fn( array $row ): string => (string) ( $row['id'] ?? '' ),
                'sortable' => 'id',
            ),
            'created_at' => array(
                'label'    => __( 'Created', 'foodbank-manager' ),
                'value'    => static fn( array $row ): string => get_date_from_gmt( (string) ( $row['created_at'] ?? '' ) ),
                'sortable' => 'created_at',
            ),
            'name' => array(
                'label' => __( 'Name', 'foodbank-manager' ),
                'value' => static function ( array $row ) use ( $unmask ): string {
                    $data = json_decode( (string) ( $row['data_json'] ?? '' ), true );
                    $pii  = Crypto::decryptSensitive( (string) ( $row['pii_encrypted_blob'] ?? '' ) );
                    $first = (string) ( $data['first_name'] ?? '' );
                    $last  = (string) ( $pii['last_name'] ?? '' );
                    if ( $last !== '' && ! $unmask ) {
                        $last = mb_substr( $last, 0, 1 ) . '***';
                    }
                    return trim( $first . ' ' . $last );
                },
            ),
            'email' => array(
                'label' => __( 'Email', 'foodbank-manager' ),
                'value' => static function ( array $row ) use ( $unmask ): string {
                    $pii   = Crypto::decryptSensitive( (string) ( $row['pii_encrypted_blob'] ?? '' ) );
                    $email = (string) ( $pii['email'] ?? '' );
                    if ( ! $unmask ) {
                        $email = Helpers::mask_email( $email );
                    }
                    return $email;
                },
            ),
            'postcode' => array(
                'label' => __( 'Postcode', 'foodbank-manager' ),
                'value' => static function ( array $row ) use ( $unmask ): string {
                    $data     = json_decode( (string) ( $row['data_json'] ?? '' ), true );
                    $postcode = (string) ( $data['postcode'] ?? '' );
                    if ( ! $unmask ) {
                        $postcode = Helpers::mask_postcode( $postcode );
                    }
                    return $postcode;
                },
            ),
            'status' => array(
                'label'    => __( 'Status', 'foodbank-manager' ),
                'value'    => static fn( array $row ): string => (string) ( $row['status'] ?? '' ),
                'sortable' => 'status',
            ),
            'has_files' => array(
                'label' => __( 'Has Files', 'foodbank-manager' ),
                'value' => static fn( array $row ): string => ! empty( $row['has_files'] ) ? __( 'Yes', 'foodbank-manager' ) : __( 'No', 'foodbank-manager' ),
            ),
        );
    }
}
