#!/usr/bin/env bash
set -euo pipefail

BASE="${1:-origin/main}"
STANDARD="${2:-phpcs.xml}"

if ! git rev-parse --verify "$BASE" >/dev/null 2>&1; then
  BASE="HEAD~1"
fi

# Only PHP in our allowed paths
FILES=$(git diff --name-only --diff-filter=AM "$BASE"...HEAD \
  | grep -E '\.php$' \
  | grep -E '^(includes/|templates/|assets/|bin/|tests/)')

if [ -z "$FILES" ]; then
  echo "No changed PHP files to fix."
  exit 0
fi

# Apply *only* specific WPCS/Squiz sniffs we trust (no whitespace churn everywhere)
vendor/bin/phpcbf \
  --standard="$STANDARD" \
  --sniffs=Squiz.ControlStructures.ControlSignature,WordPress.WhiteSpace.OperatorSpacing \
  $FILES
