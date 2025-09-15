#!/usr/bin/env bash
set -euo pipefail

BASE="${1:-origin/main}"
STANDARD="${2:-phpcs.xml}"

FILES=$(git diff --name-only --diff-filter=AM "$BASE"...HEAD \
  | grep -E '\.php$' \
  | grep -E '^(includes/|templates/|assets/|bin/|tests/)') || true

if [ -z "${FILES:-}" ]; then
  echo "No changed PHP files to fix."
  exit 0
fi

vendor/bin/phpcbf \
  --standard="$STANDARD" \
  --sniffs=Squiz.ControlStructures.ControlSignature,WordPress.WhiteSpace.OperatorSpacing \
  $FILES
