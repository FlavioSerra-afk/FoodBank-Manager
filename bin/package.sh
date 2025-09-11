#!/usr/bin/env bash
# Package builder: enforces foodbank-manager/ slug and compiles .mo when msgfmt is available.
set -euo pipefail

SLUG="foodbank-manager"
DIST="dist"
WORK="$DIST/$SLUG"
BUILD="build"

rm -rf "$WORK" "$DIST/$SLUG.zip"
mkdir -p "$WORK" "$BUILD"

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
  --exclude "build" \
  --exclude ".DS_Store" \
  --exclude "*.zip" \
  --exclude "$SLUG.php" \
  ./ "$WORK/"

# Compile all translations if msgfmt is available (soft fail otherwise)
if command -v msgfmt >/dev/null 2>&1; then
  echo "[i18n] Compiling .mo files..."
  for po in "$WORK"/languages/*.po "$WORK"/languages/*/*.po; do
    [ -f "$po" ] || continue
    msgfmt -o "${po%.po}.mo" "$po" || true
  done
else
  echo "[i18n] msgfmt not found; skipping .mo compile (POT/PO still included)."
fi

# Build the ZIP with a stable top-level directory
( cd "$DIST" && zip -rq "$SLUG.zip" "$SLUG" )

# Verify first ZIP entry is the slug directory
set +o pipefail
FIRST_ENTRY=$(zipinfo -1 "$DIST/$SLUG.zip" | head -n1)
echo "$FIRST_ENTRY" > "$BUILD/zip-root.txt"
set -o pipefail
if [[ "$FIRST_ENTRY" != "$SLUG/" ]]; then
  echo "Error: ZIP first entry '$FIRST_ENTRY' is not '$SLUG/'" >&2
  exit 1
fi

# Ensure main file exists inside the ZIP
set +o pipefail
if unzip -l "$DIST/$SLUG.zip" | grep -F "$SLUG/$SLUG.php" > "$BUILD/zip-main.txt"; then
  true
else
  echo "Error: $SLUG/$SLUG.php missing from ZIP" >&2
  exit 1
fi
set -o pipefail

echo "Built $DIST/$SLUG.zip"
