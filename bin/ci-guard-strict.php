<?php
$strict = array();
$flag   = false;
foreach ( file( 'Docs/CS-Backlog.md' ) as $ln ) {
	if ( preg_match( '~^Strict~', $ln ) ) {
		$flag = true;
		continue; }
	if ( preg_match( '~^Legacy~', $ln ) ) {
		$flag = false; }
	if ( $flag && preg_match( '~^\s*(includes|foodbank-manager\.php)~', $ln ) ) {
		$strict[] = trim( $ln ); }
}
$pattern = '~phpcs:(ignore|disable)~i';
$bad     = array();
foreach ( $strict as $file ) {
	$body = @file_get_contents( $file ) ?: '';
	if ( preg_match( $pattern, $body ) ) {
		$bad[] = $file; }
}
if ( $bad ) {
	fwrite( STDERR, "Strict files contain suppressions:\n- " . implode( "\n- ", $bad ) . "\n" );
	exit( 1 );
}
echo "Strict set contains no suppressions.\n";
