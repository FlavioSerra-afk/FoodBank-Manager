<?php
/**
 * Welcome mailer.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FoodBankManager\Email;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Throwable;
use function __;
use function implode;
use function is_readable;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function sprintf;

/**
 * Composes and sends welcome emails with QR tokens.
 */
final class WelcomeMailer {

	private const TEMPLATE = 'templates/emails/welcome.php';
	private const QR_SIZE  = 280;

	/**
	 * Send the welcome email.
	 *
	 * @param string $email            Recipient email address.
	 * @param string $first_name       Recipient first name.
	 * @param string $member_reference Canonical member reference string.
	 * @param string $token            Raw token issued for the member.
	 */
	public function send( string $email, string $first_name, string $member_reference, string $token ): bool {
		$qr_data_uri = $this->build_qr_data_uri( $token );

		$context = array(
			'first_name'       => $first_name,
			'member_reference' => $member_reference,
			'qr_data_uri'      => $qr_data_uri,
		);

		$body = $this->render_template( $context );

		if ( '' === $body ) {
			$body    = $this->fallback_body( $member_reference );
			$headers = array();
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		}

		$subject = __( 'Your food bank check-in QR code', 'foodbank-manager' );

		return wp_mail( $email, $subject, $body, $headers );
	}

		/**
		 * Render the QR token as a data URI for embedding.
		 *
		 * @param string $token Raw token issued for the member.
		 */
	private function build_qr_data_uri( string $token ): string {
		try {
			$builder = new Builder(
				writer: new PngWriter(),
				data: $token,
				encoding: new Encoding( 'UTF-8' ),
				errorCorrectionLevel: ErrorCorrectionLevel::High,
				size: self::QR_SIZE,
				margin: 12,
				roundBlockSizeMode: RoundBlockSizeMode::Margin,
			);

			$result = $builder->build();

			return $result->getDataUri();
		} catch ( Throwable $exception ) {
			unset( $exception );

			return '';
		}
	}

	/**
	 * Render the welcome template with context.
	 *
	 * @param array<string, mixed> $context Template context values.
	 */
	private function render_template( array $context ): string {
		$template = FBM_PATH . self::TEMPLATE;

		if ( ! is_readable( $template ) ) {
			return '';
		}

		ob_start();

		$data = $context;
		include $template;

		$output = ob_get_clean();

		return is_string( $output ) ? $output : '';
	}

		/**
		 * Produce a plain-text fallback when the template cannot be rendered.
		 *
		 * @param string $member_reference Canonical member reference string.
		 */
	private function fallback_body( string $member_reference ): string {
		$lines = array(
			__( 'Welcome!', 'foodbank-manager' ),
			__( 'Show this email during check-in or share your reference code with a volunteer.', 'foodbank-manager' ),
			sprintf(
				/* translators: %s: Member reference code. */
				__( 'Reference code: %s', 'foodbank-manager' ),
				$member_reference
			),
		);

		return implode( "\n\n", $lines );
	}
}
