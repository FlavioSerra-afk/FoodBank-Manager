<?php
declare(strict_types=1);

/**
 * Build PHPCS ignore dashboard from:
 *  - analysis/phpcs-ignored.json (PHPCS run with --ignore-annotations)
 *  - analysis/phpcs-ignores.txt  (grep of phpcs:ignore etc.)
 * Writes: Docs/PHPCS-Ignores.md
 */

const REPORT_JSON = __DIR__ . '/../analysis/phpcs-ignored.json';
const REPORT_IGN  = __DIR__ . '/../analysis/phpcs-ignores.txt';
const OUT_MD      = __DIR__ . '/../Docs/PHPCS-Ignores.md';

@mkdir( dirname( OUT_MD ), 0775, true );

function readJson( string $file ): array {
	if ( ! file_exists( $file ) ) {
		return array();
	}
	$raw  = file_get_contents( $file ) ?: '';
	$data = json_decode( $raw, true );
	return is_array( $data ) ? $data : array();
}

/** @return array<int, array{file:string,line:int,code:string,message:string,sniff:string}> */
function flattenPhpcs( array $json ): array {
	$out = array();
	foreach ( ( $json['files'] ?? array() ) as $file => $payload ) {
		foreach ( ( $payload['messages'] ?? array() ) as $m ) {
			$out[] = array(
				'file'    => $file,
				'line'    => (int) ( $m['line'] ?? 0 ),
				'code'    => (string) ( $m['source'] ?? '' ),
				'message' => (string) ( $m['message'] ?? '' ),
				'sniff'   => (string) ( $m['source'] ?? '' ),
			);
		}
	}
	return $out;
}

function groupCount( array $rows, string $key ): array {
	$c = array();
	foreach ( $rows as $r ) {
		$k       = (string) ( $r[ $key ] ?? '' );
		$c[ $k ] = ( $c[ $k ] ?? 0 ) + 1; }
	arsort( $c );
	return $c;
}

/** Map PHPCS sniffs to fix recipes */
function recipe( string $sniff ): string {
	$map = array(
		'WordPress.Security.EscapeOutput.OutputNotEscaped' => '- **Fix:** Escape text with `esc_html()`, attributes with `esc_attr()`, URLs with `esc_url()`. For intentional HTML, sanitize with `wp_kses_post()` and echo with a one-line justified ignore only if PHPCS still complains.',
		'WordPress.Security.NonceVerification.Recommended' => '- **Fix:** For POST/mutations add `wp_nonce_field()` + `check_admin_referer()`. For read-only GET filters sanitize and keep a one-line justified ignore.',
		'WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare' => "- **Fix:** Build strict placeholders for `IN (...)` (e.g., `implode(',', array_fill(0, count(\$ids), '%d'))`), guard for empty arrays, and prepare values. Keep a one-line ignore only if PHPCS remains noisy.",
		'WordPress.Security.ValidatedSanitizedInput.InputNotSanitized' => '- **Fix:** Wrap `$_GET/$_POST` with `wp_unslash()` + `sanitize_text_field()` / `absint()` / whitelist enums.',
		'WordPress.Security.ValidatedSanitizedInput.MissingUnslash' => '- **Fix:** Use `wp_unslash()` before sanitizing superglobals.',
	);
	foreach ( $map as $k => $v ) {
		if ( str_starts_with( $sniff, $k ) ) {
			return $v;
		}
	}
	return '- **Fix:** Apply appropriate sanitization/escaping or add targeted justification.';
}

$json   = readJson( REPORT_JSON );
$rows   = flattenPhpcs( $json );
$byCode = groupCount( $rows, 'code' );
$byFile = groupCount( $rows, 'file' );

// Load ignores list
$ignores = file_exists( REPORT_IGN ) ? file( REPORT_IGN, FILE_IGNORE_NEW_LINES ) : array();

$md  = "# PHPCS Ignores & Suppressed Issues Dashboard\n\n";
$md .= '_Generated: ' . gmdate( 'Y-m-d H:i:s' ) . "Z\n\n";
$md .= "This report shows which issues are currently suppressed via `phpcs:ignore` and what would fail if annotations were disabled.\n\n";

$md .= "## Snapshot\n\n";
$md .= '- Suppressed issues (from `--ignore-annotations` run): **' . count( $rows ) . "**\n";
$md .= '- Ignore annotations present: **' . count( $ignores ) . "** lines\n\n";

$md .= "## Top sniffs by count\n\n";
$md .= "| Sniff | Count | Recipe |\n|---|---:|---|\n";
foreach ( array_slice( $byCode, 0, 15, true ) as $sniff => $count ) {
	$md .= "| `{$sniff}` | {$count} | " . str_replace( "\n", ' ', recipe( $sniff ) ) . " |\n";
}
$md .= "\n";

$md .= "## Top files by suppressed issues\n\n";
$md .= "| File | Count |\n|---|---:|\n";
foreach ( array_slice( $byFile, 0, 20, true ) as $file => $count ) {
	$md .= "| {$file} | {$count} |\n";
}
$md .= "\n";

$md .= "## All ignores (locations)\n\n";
if ( $ignores ) {
	$md .= "```\n" . implode( "\n", $ignores ) . "```\n\n";
} else {
	$md .= "_No inline PHPCS ignores found._\n\n";
}

$md .= "## Detailed suppressed issues\n\n";
$md .= "| File | Line | Sniff | Message |\n|---|---:|---|---|\n";
foreach ( $rows as $r ) {
	$md .= "| {$r['file']} | {$r['line']} | `{$r['sniff']}` | " . str_replace( '|', '\\|', $r['message'] ) . " |\n";
}

file_put_contents( OUT_MD, $md );
echo 'Wrote ' . OUT_MD . PHP_EOL;
