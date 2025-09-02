#!/usr/bin/env php
<?php
declare(strict_types=1);

use Symfony\Component\Process\Process;

function run(string $cmd): string {
    $p = Process::fromShellCommandline($cmd);
    $p->mustRun();
    return trim($p->getOutput());
}

$root = dirname(__DIR__);
require $root . "/vendor/autoload.php";
chdir($root);

// Find last tag
$lastTag = '';
try { $lastTag = run('git describe --tags --abbrev=0'); } catch (\Throwable $e) { $lastTag = ''; }

$range = $lastTag ? "$lastTag..HEAD" : '';
$logCmd = $range
  ? "git log $range --pretty=format:%s"
  : "git log --pretty=format:%s";
$lines = explode("\n", run($logCmd));

$groups = [
  'feat' => [], 'fix' => [], 'perf' => [], 'refactor' => [],
  'docs' => [], 'chore' => [], 'test' => [], 'build' => [],
];
foreach ($lines as $l) {
    $l = trim($l);
    if ($l === '') continue;
    if (preg_match('/^(feat|fix|perf|refactor|docs|chore|test|build)(\([\w\-]+\))?:\s*(.+)$/i', $l, $m)) {
        $type = strtolower($m[1]); $msg = $m[3];
        $groups[$type][] = $msg;
    } else {
        $groups['chore'][] = $l;
    }
}

$version = json_decode(file_get_contents('composer.json'), true)['version'] ?? '0.0.0';
$date = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');

$chunk = "## v$version — $date\n\n";
$labels = [
  'feat' => 'Features', 'fix' => 'Fixes', 'perf' => 'Performance',
  'refactor' => 'Refactors', 'docs' => 'Docs', 'test' => 'Tests', 'build'=>'Build', 'chore' => 'Chores',
];

foreach ($labels as $key => $title) {
    if (!empty($groups[$key])) {
        $chunk .= "### $title\n";
        foreach ($groups[$key] as $msg) {
            $chunk .= "- $msg\n";
        }
        $chunk .= "\n";
    }
}

$path = $root.'/CHANGELOG.md';
$existing = file_exists($path) ? file_get_contents($path) : "# Changelog\n\n";
file_put_contents($path, $existing . $chunk);

echo "CHANGELOG updated for v$version\n";
