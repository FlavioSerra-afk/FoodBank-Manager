#!/usr/bin/env bash
# Package builder: enforces foodbank-manager/ slug and compiles .mo when msgfmt is available.
set -euo pipefail

SLUG="foodbank-manager"
DIST="dist"
WORK="$DIST/$SLUG"
BUILD="build"

PLUGIN_VERSION=$(grep -E "^\s*\*\s*Version:" -m1 "$SLUG.php" | sed -E "s/.*Version:\s*([0-9A-Za-z._+-]+).*/\\1/")
CORE_VERSION=$(grep -E "VERSION\s*=\s*'[^']+'" -m1 includes/Core/class-plugin.php | sed -E "s/.*'([^']+)'.*/\\1/")
README_VERSION=$(grep -E "^Stable tag:" -m1 readme.txt | sed -E "s/^Stable tag:\s*([0-9A-Za-z._+-]+).*/\\1/")

if [[ -z "$PLUGIN_VERSION" || -z "$CORE_VERSION" || -z "$README_VERSION" ]]; then
  echo "Error: Unable to resolve plugin, core, or readme version markers" >&2
  exit 1
fi

if [[ "$PLUGIN_VERSION" != "$CORE_VERSION" || "$PLUGIN_VERSION" != "$README_VERSION" ]]; then
  echo "Error: Version mismatch. Plugin=$PLUGIN_VERSION, Core=$CORE_VERSION, Readme=$README_VERSION" >&2
  exit 1
fi

mkdir -p "$BUILD"
echo "Plugin version: $PLUGIN_VERSION" > "$BUILD/version.txt"

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
  --exclude ".githooks" \
  --exclude "node_modules" \
  --exclude "vendor/bin" \
  --exclude "tests" \
  --exclude "dist" \
  --exclude "build" \
  --exclude "analysis" \
  --exclude ".DS_Store" \
  --exclude "*.zip" \
  --exclude "$SLUG.php" \
  ./ "$WORK/"

# Production install without dev dependencies unless instructed to reuse local vendor
rm -rf "$WORK/vendor"
if [[ -n "${FBM_PACKAGE_USE_LOCAL_VENDOR:-}" ]]; then
  echo "[build] Using local vendor directory (FBM_PACKAGE_USE_LOCAL_VENDOR set)"
  rsync -a --delete vendor/ "$WORK/vendor/"
else
  ( cd "$WORK" && composer install --no-dev --optimize-autoloader && composer dump-autoload --optimize --classmap-authoritative )
fi

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

zipinfo -1 "$DIST/$SLUG.zip" | sort > "$BUILD/zip-contents.txt"
cp "$BUILD/zip-contents.txt" "$DIST/$SLUG-manifest.txt"

# Verify first ZIP entry is the slug directory
set +o pipefail
FIRST_ENTRY=$(head -n1 "$BUILD/zip-contents.txt")
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
