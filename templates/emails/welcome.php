<?php
/**
 * Welcome email template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) && ! defined( 'FBM_TESTING' ) ) {
        exit;
}

/**
 * Template context.
 *
 * @var array<string, mixed> $data
 */
$first_name       = isset( $data['first_name'] ) ? (string) $data['first_name'] : '';
$member_reference = isset( $data['member_reference'] ) ? (string) $data['member_reference'] : '';
$qr_data_uri      = isset( $data['qr_data_uri'] ) ? (string) $data['qr_data_uri'] : '';
$token_payload    = isset( $data['token_payload'] ) ? (string) $data['token_payload'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php esc_html_e( 'Your food bank check-in QR code', 'foodbank-manager' ); ?></title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; color: #1f2933; margin: 0; padding: 0;">
	<div style="padding: 24px;">
		<p style="margin-top: 0;">
			<?php if ( '' !== $first_name ) : ?>
				<?php
				printf(
					/* translators: %s: Recipient first name. */
					esc_html__( 'Hi %s,', 'foodbank-manager' ),
					esc_html( $first_name )
				);
				?>
			<?php else : ?>
				<?php esc_html_e( 'Hello,', 'foodbank-manager' ); ?>
			<?php endif; ?>
		</p>

		<p>
			<?php esc_html_e( 'Thank you for registering with our food bank program. Bring this QR code to check in quickly at your next visit.', 'foodbank-manager' ); ?>
		</p>

                <?php if ( '' !== $qr_data_uri ) : ?>
                        <div style="text-align: center; margin: 32px 0;">
                                <img
                                        src="<?php echo esc_url( $qr_data_uri ); ?>"
                                        alt="<?php esc_attr_e( 'Food bank check-in QR code', 'foodbank-manager' ); ?>"
                                        style="max-width: 240px; height: auto; border: 8px solid #e5e7eb; border-radius: 8px;"
                                        <?php if ( '' !== $token_payload ) : ?>data-fbm-token="<?php echo $token_payload; ?>"<?php endif; ?>
                                />
                        </div>
                <?php endif; ?>

                <?php if ( '' !== $token_payload ) : ?>
                        <p>
                                <?php esc_html_e( 'If you cannot scan the QR code, share this reference code with a volunteer:', 'foodbank-manager' ); ?>
                        </p>

                        <p style="font-size: 20px; font-weight: bold; letter-spacing: 1px;">
                                <code style="font-family: 'Courier New', Courier, monospace; background-color: #f3f4f6; padding: 8px 12px; border-radius: 6px; display: inline-block;"><?php echo $token_payload; ?></code>
                        </p>
                <?php else : ?>
                        <p>
                                <?php esc_html_e( 'If you cannot scan the QR code, share this reference code with a volunteer:', 'foodbank-manager' ); ?>
                        </p>

                        <p style="font-size: 20px; font-weight: bold; letter-spacing: 1px;">
                                <?php echo esc_html( $member_reference ); ?>
                        </p>
                <?php endif; ?>

		<p style="margin-bottom: 0;">
			<?php esc_html_e( 'See you soon!', 'foodbank-manager' ); ?>
		</p>
	</div>
</body>
</html>
