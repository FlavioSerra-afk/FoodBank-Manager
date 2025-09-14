#!/usr/bin/env php
<?php
declare(strict_types=1);
/* usage: php bin/bump-version.php [patch|minor|major] (default: patch) */
$mode = $argv[1] ?? 'patch';
if ( ! in_array( $mode, array( 'patch', 'minor', 'major' ), true ) ) {
	fwrite( STDERR, "Invalid mode\n" );
	exit( 1 ); }
function r( string $p ) {
	if ( ! file_exists( $p ) ) {
		throw new RuntimeException( "Missing $p" );
	} return file_get_contents( $p ); }
function w( string $p, string $c ) {
	if ( false === file_put_contents( $p, $c ) ) {
		throw new RuntimeException( "Write $p failed" );
	} }
function bump( string $v, string $m ): string {
	if ( ! preg_match( '/^(\d+)\.(\d+)\.(\d+)$/', $v, $x ) ) {
		throw new RuntimeException( "Bad semver $v" );
	} [$a, $ma, $mi, $pa] = $x;
	$ma                   = (int) $ma;
	$mi                   = (int) $mi;
	$pa                   = (int) $pa;
	if ( $m === 'major' ) {
		++$ma;
		$mi = 0;
		$pa = 0;
	} elseif ( $m === 'minor' ) {
		++$mi;
		$pa = 0;
	} else {
		++$pa;
	} return "$ma.$mi.$pa"; }

$plugin = r( 'includes/Core/Plugin.php' );
$found  = null;
foreach ( array(
	"/define\(\s*'FBM_VER'\s*,\s*'(\d+\.\d+\.\d+)'\s*\)/",
	"/const\s+FBM_VER\s*=\s*'(\d+\.\d+\.\d+)'\s*;/",
	"/FBM_VER\s*=\s*'(\d+\.\d+\.\d+)'\s*;/",
) as $rx ) {
	if ( preg_match( $rx, $plugin, $m ) ) {
		$found = $m[1];
		break; }
}
if ( ! $found ) {
	$main = r( 'foodbank-manager.php' );
	if ( preg_match( '/^\s*\*\s*Version:\s*(\d+\.\d+\.\d+)$/m', $main, $m ) ) {
		$found = $m[1];
	}
}
if ( ! $found ) {
	throw new RuntimeException( 'Version not found' );
}

$new = bump( $found, $mode );

/* Plugin.php */
$plugin = preg_replace(
	array( "/(define\(\s*'FBM_VER'\s*,\s*')\d+\.\d+\.\d+('\s*\))/", "/(const\s+FBM_VER\s*=\s*')\d+\.\d+\.\d+('\s*;)/", "/(FBM_VER\s*=\s*')\d+\.\d+\.\d+('\s*;)/" ),
	array( "\\$1{$new}\\$2", "\\$1{$new}\\$2", "\\$1{$new}\\$2" ),
	$plugin
);
w( 'includes/Core/Plugin.php', $plugin );

/* main header */
$main = r( 'foodbank-manager.php' );
$main = preg_replace( '/^(\s*\*\s*Version:\s*)\d+\.\d+\.\d+$/m', "\\$1{$new}", $main );
w( 'foodbank-manager.php', $main );

/* composer.json */
$cmp            = json_decode( r( 'composer.json' ), true, 512, JSON_THROW_ON_ERROR );
$cmp['version'] = $new;
w( 'composer.json', json_encode( $cmp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "\n" );

/* readme Stable tag */
$readme = r( 'readme.txt' );
$readme = preg_replace( '/^(Stable tag:\s*)\d+\.\d+\.\d+$/m', "\\$1{$new}", $readme );
w( 'readme.txt', $readme );

/* CHANGELOG stub */
$chg = r( 'CHANGELOG.md' );
if ( strpos( $chg, "## {$new}" ) === false ) {
	$today = ( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )->format( 'Y-m-d' );
	$stub  = "## {$new} — {$today}\n- Bump version\n\n";
	w( 'CHANGELOG.md', $stub . $chg ); }

fwrite( STDOUT, "Bumped: {$found} → {$new}\n" );
