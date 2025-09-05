#!/usr/bin/env php
<?php
declare(strict_types=1);

use Symfony\Component\Process\Process;

function run( string $cmd, ?string $cwd = null ): string {
	$p = Process::fromShellCommandline( $cmd, $cwd ?? getcwd() );
	$p->mustRun();
	return trim( $p->getOutput() );
}

function readJson( string $path ): array {
	return json_decode( file_get_contents( $path ), true, 512, JSON_THROW_ON_ERROR );
}
function writeJson( string $path, array $data ): void {
	file_put_contents( $path, json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL );
}

$root = dirname( __DIR__ );
require $root . '/vendor/autoload.php';
$pluginFile   = $root . '/foodbank-manager.php';
$pluginClass  = $root . '/includes/Core/Plugin.php';
$composerFile = $root . '/composer.json';

$args = $argv;
array_shift( $args );
if ( ! $args ) {
	fwrite( STDERR, "Usage: bump-version [patch|minor|major|set X.Y.Z] [--no-tag]\n" );
	exit( 1 );
}
$mode  = $args[0];
$noTag = in_array( '--no-tag', $args, true );

$composer = readJson( $composerFile );
$current  = $composer['version'] ?? '0.0.0';

function semverNext( string $v, string $mode ): string {
	if ( preg_match( '/^\d+\.\d+\.\d+$/', $v ) !== 1 ) {
		throw new RuntimeException( "Bad version: $v" );
	}
	[$M, $m, $p] = array_map( 'intval', explode( '.', $v ) );
	return match ( $mode ) {
		'patch' => sprintf( '%d.%d.%d', $M, $m, $p + 1 ),
		'minor' => sprintf( '%d.%d.%d', $M, $m + 1, 0 ),
		'major' => sprintf( '%d.%d.%d', $M + 1, 0, 0 ),
		default => throw new RuntimeException( "Unknown mode: $mode" ),
	};
}

if ( $mode === 'set' ) {
	$next = $args[1] ?? null;
	if ( ! $next || preg_match( '/^\d+\.\d+\.\d+$/', $next ) !== 1 ) {
		fwrite( STDERR, "Usage: bump-version set X.Y.Z\n" );
		exit( 1 );
	}
} else {
	$next = semverNext( $current, $mode );
}

// 1) Update composer.json
$composer['version'] = $next;
writeJson( $composerFile, $composer );

// 2) Update plugin header
$pluginSrc = file_get_contents( $pluginFile );
$pluginSrc = preg_replace( '/^[ \t\/*#@]*Version:\s*\K[0-9]+\.[0-9]+\.[0-9]+/mi', $next, $pluginSrc, 1, $cnt1 );
if ( ! $cnt1 ) {
	fwrite( STDERR, "Failed to update plugin header version\n" );
	exit( 1 ); }
file_put_contents( $pluginFile, $pluginSrc );

// 3) Update Plugin::VERSION
$cls = file_get_contents( $pluginClass );
$cls = preg_replace( "/VERSION\s*=\s*'(\d+\.\d+\.\d+)'/", "VERSION = '$next'", $cls, 1, $cnt2 );
if ( ! $cnt2 ) {
        fwrite( STDERR, "Failed to update Plugin::VERSION\n" );
	exit( 1 ); }
file_put_contents( $pluginClass, $cls );

// 4) Git commit + tag
run( 'git add composer.json foodbank-manager.php includes/Core/Plugin.php', $root );
run( sprintf( 'git commit -m "chore(release): bump version to %s"', $next ), $root );
if ( ! $noTag ) {
	run( sprintf( 'git tag -a v%s -m "Release %s"', $next, $next ), $root );
	echo "Tagged v$next\n";
} else {
	echo "Bumped to $next (no tag)\n";
}
