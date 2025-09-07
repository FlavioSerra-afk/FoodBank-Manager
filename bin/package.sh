#!/usr/bin/env bash
set -euo pipefail

SLUG="foodbank-manager"
DIST="dist"
WORK="$DIST/$SLUG"

rm -rf "$WORK" "$DIST/$SLUG.zip"
mkdir -p "$WORK"

# Ensure main file exists in repository root and copy it into the package first
if [[ ! -f "$SLUG.php" ]]; then
  echo "Error: Missing $SLUG.php in repository root" >&2
  exit 1
fi
cp "$SLUG.php" "$WORK/$SLUG.php"

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
  --exclude "$SLUG.php" \
  ./ "$WORK/"

# Build the ZIP with a stable top-level directory
( cd "$DIST" && zip -rq "$SLUG.zip" "$SLUG" )

# Verify ZIP root directory
FIRST_ENTRY=$(unzip -l "$DIST/$SLUG.zip" | awk 'NR==4 {print $4}')
if [[ "$FIRST_ENTRY" != "$SLUG/" ]]; then
  echo "Error: ZIP root is '$FIRST_ENTRY' (expected '$SLUG/')" >&2
  exit 1
fi

# Ensure main file exists inside the ZIP
set +e
set +o pipefail
unzip -l "$DIST/$SLUG.zip" | grep -Fq "$SLUG/$SLUG.php"
FOUND=$?
set -e
set -o pipefail
if [[ $FOUND -ne 0 ]]; then
  echo "Error: $SLUG/$SLUG.php missing from ZIP" >&2
  exit 1
fi

echo "Built $DIST/$SLUG.zip"
