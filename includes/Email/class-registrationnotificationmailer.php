<?php
/**
 * Registration notification mailer.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Email;

use function __;
use function admin_url;
use function get_option;
use function implode;
use function is_email;
use function sanitize_email;
use function sprintf;
use function wp_mail;

/**
 * Sends notifications to administrators when new registrations arrive.
 */
final class RegistrationNotificationMailer {
        /**
         * Dispatch a notification email for the provided registration context.
         *
         * @param string $member_reference Canonical member reference.
         * @param string $first_name       Member first name.
         * @param string $last_initial     Member last initial.
         * @param string $email            Member email address.
         * @param string $status           Registration status (active|pending).
         */
        public function send( string $member_reference, string $first_name, string $last_initial, string $email, string $status ): void {
                $recipient = sanitize_email( (string) get_option( 'admin_email', '' ) );

                if ( '' === $recipient || ! is_email( $recipient ) ) {
                        return;
                }

                $subject = __( 'New food bank registration submitted', 'foodbank-manager' );

                $status_label = 'active' === $status
                        ? __( 'Auto-approved', 'foodbank-manager' )
                        : __( 'Pending review', 'foodbank-manager' );

                $lines = array(
                        __( 'A new registration has been received.', 'foodbank-manager' ),
                        sprintf(
                                /* translators: %s: Registration status label. */
                                __( 'Status: %s', 'foodbank-manager' ),
                                $status_label
                        ),
                        sprintf(
                                /* translators: %s: Canonical member reference identifier. */
                                __( 'Member reference: %s', 'foodbank-manager' ),
                                $member_reference
                        ),
                );

                if ( '' !== $first_name ) {
                        $lines[] = sprintf(
                                /* translators: 1: Member first name, 2: Member last initial. */
                                __( 'Name: %1$s %2$s.', 'foodbank-manager' ),
                                $first_name,
                                $last_initial
                        );
                }

                $lines[] = '';
                $lines[] = sprintf(
                        /* translators: %s: Admin URL for reviewing the application. */
                        __( 'Review the application: %s', 'foodbank-manager' ),
                        admin_url( 'admin.php?page=fbm-members' )
                );

                wp_mail( $recipient, $subject, implode( "\n", $lines ) );
        }
}
