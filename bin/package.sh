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
if [[ ! -f "$WORK/$SLUG.php" ]]; then
  echo "Error: Expected main file at $SLUG/$SLUG.php" >&2
  exit 1
fi

# Build the ZIP with a stable top-level directory
( cd "$DIST" && zip -rq "$SLUG.zip" "$SLUG" )

# Verify ZIP root directory
FIRST_ENTRY=$(unzip -l "$DIST/$SLUG.zip" | awk 'NR==4 {print $4}')
if [[ "$FIRST_ENTRY" != "$SLUG/" ]]; then
  echo "Error: ZIP root is '$FIRST_ENTRY' (expected '$SLUG/')" >&2
  exit 1
fi

# Ensure main file exists inside the ZIP
if ! unzip -l "$DIST/$SLUG.zip" | awk '{print $4}' | grep -qx "$SLUG/$SLUG.php"; then
  echo "Error: $SLUG/$SLUG.php missing from ZIP" >&2
  exit 1
fi

echo "Built $DIST/$SLUG.zip"
