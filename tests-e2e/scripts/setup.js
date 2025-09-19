#!/usr/bin/env node
'use strict';

const { execSync } = require('child_process');
const path = require('path');

const repoRoot = path.resolve(__dirname, '..', '..');

function run(command) {
  execSync(command, { stdio: 'inherit', cwd: repoRoot });
}

run('wp-env start');

try {
  run('wp-env run tests-cli wp plugin is-active foodbank-manager');
} catch (error) {
  run('wp-env run tests-cli wp plugin activate foodbank-manager');
}

run('wp-env run tests-cli wp eval-file tests-e2e/scripts/bootstrap.php');
run('wp-env run tests-cli wp eval-file tests-e2e/scripts/seed.php');
run('npx playwright install --with-deps chromium');
