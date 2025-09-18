#!/usr/bin/env bash
set -euo pipefail
BASE="${1:-}"
if [[ -z "$BASE" ]]; then
  if git rev-parse --verify --quiet origin/main >/dev/null; then BASE=origin/main
  elif git rev-parse --verify --quiet origin/master >/dev/null; then BASE=origin/master
  else BASE=$(git merge-base HEAD "$(git rev-parse --abbrev-ref HEAD)~1"); fi
fi
mapfile -t FILES < <(git diff --name-only --diff-filter=ACM "$BASE"...HEAD | grep -E '\.php$' || true)
if [[ ${#FILES[@]} -eq 0 ]]; then echo "No changed PHP files. Skipping PHPCS."; exit 0; fi
echo "Running PHPCS on changed files vs $BASE:"; printf '%s\n' "${FILES[@]}"
if [[ -f phpcs.xml || -f phpcs.xml.dist ]]; then vendor/bin/phpcs "${FILES[@]}"; else vendor/bin/phpcs --standard=WordPress "${FILES[@]}"; fi
