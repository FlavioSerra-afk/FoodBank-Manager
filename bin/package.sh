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

# Verify first ZIP entry is the slug directory
set +o pipefail
FIRST_ENTRY=$(zipinfo -1 "$DIST/$SLUG.zip" | head -n1)
set -o pipefail
if [[ "$FIRST_ENTRY" != "$SLUG/" ]]; then
  echo "Error: ZIP first entry '$FIRST_ENTRY' is not '$SLUG/'" >&2
  exit 1
fi

# Ensure main file exists inside the ZIP
set +o pipefail
if unzip -l "$DIST/$SLUG.zip" | grep -Fq "$SLUG/$SLUG.php"; then
  true
else
  echo "Error: $SLUG/$SLUG.php missing from ZIP" >&2
  exit 1
fi
set -o pipefail

echo "Built $DIST/$SLUG.zip"
