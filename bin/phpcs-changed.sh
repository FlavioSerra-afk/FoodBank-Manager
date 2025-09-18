#!/usr/bin/env bash
set -euo pipefail

BASE="${1:-origin/main}"
STANDARD="${2:-phpcs.xml}"

if ! git rev-parse --verify "$BASE" >/dev/null 2>&1; then
  if git rev-parse --verify main >/dev/null 2>&1; then
    BASE="main"
  else
    BASE="HEAD^"
  fi
fi

FILES=$(git diff --name-only --diff-filter=AM "$BASE"...HEAD \
  | grep -E '\.(php|phtml)$' \
  | grep -E '^(includes/|templates/|foodbank-manager.php|uninstall.php)') || true

if [ -z "${FILES:-}" ]; then
  echo "No changed PHP files to lint."
  exit 0
fi

vendor/bin/phpcs \
  --standard="$STANDARD" \
  --report=summary \
  $FILES
