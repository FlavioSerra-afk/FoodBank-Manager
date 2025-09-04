#!/usr/bin/env bash
set -euo pipefail

SLUG="foodbank-manager"
DIST="dist"
WORK="$DIST/$SLUG"

rm -rf "$WORK" "$DIST/$SLUG.zip"
mkdir -p "$WORK"

# Copy tracked plugin files into a stable folder name
rsync -a --delete \
  --exclude ".git" \
  --exclude ".github" \
  --exclude "node_modules" \
  --exclude "vendor/bin" \
  --exclude "tests" \
  --exclude "dist" \
  --exclude ".DS_Store" \
  --exclude "*.zip" \
  ./ "$WORK/"

# Ensure main file exists where WP expects it
test -f "$WORK/$SLUG.php"

# Build the ZIP with a stable top-level directory
( cd "$DIST" && zip -rq "$SLUG.zip" "$SLUG" )
echo "Built $DIST/$SLUG.zip"
