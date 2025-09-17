<?php
/**
 * Prepare CHANGELOG.md for a release by ensuring the current version heading exists.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$changelog_path = $root . '/CHANGELOG.md';
$plugin_path = $root . '/foodbank-manager.php';

$plugin_contents = @file_get_contents($plugin_path);
if (false === $plugin_contents) {
    fwrite(STDERR, "Unable to read plugin file to determine version." . PHP_EOL);
    exit(1);
}

if (!preg_match('/^\s*\*\s*Version:\s*([^\s]+)$/m', $plugin_contents, $matches)) {
    fwrite(STDERR, "Plugin version header not found." . PHP_EOL);
    exit(1);
}

$version = trim($matches[1]);
$date    = gmdate('Y-m-d');

$changelog = @file_get_contents($changelog_path);
if (false === $changelog) {
    fwrite(STDERR, "Unable to read CHANGELOG.md." . PHP_EOL);
    exit(1);
}

if (!preg_match('/^# Changelog/m', $changelog)) {
    $changelog = "# Changelog\n\n## [Unreleased]\n\n- _No unreleased changes._\n\n" . $changelog;
}

if (!preg_match('/## \[Unreleased\]/m', $changelog)) {
    $changelog = preg_replace('/^# Changelog\n+/m', "# Changelog\n\n## [Unreleased]\n\n- _No unreleased changes._\n\n", $changelog, 1);
}

$heading = '## [' . $version . '] - ' . $date;

if (strpos($changelog, $heading) !== false) {
    file_put_contents($changelog_path, $changelog);
    exit(0);
}

$replacement = "## [Unreleased]\n\n- _No unreleased changes._\n\n" . $heading . "\n\n- _No documented changes._\n\n";

$count = 0;
$updated = preg_replace('/## \[Unreleased\][\s\S]*?(?=^## \[|\z)/m', $replacement, $changelog, 1, $count);

if (0 === $count || null === $updated) {
    $changelog = "# Changelog\n\n" . $replacement . trim($changelog) . "\n";
} else {
    $changelog = $updated;
}

file_put_contents($changelog_path, $changelog);
