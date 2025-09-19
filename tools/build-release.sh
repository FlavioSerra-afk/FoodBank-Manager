#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"

cd "$ROOT_DIR"

if [[ -z "${FBM_RELEASE_SKIP_INSTALL:-}" ]]; then
  composer install --no-interaction --no-progress --prefer-dist
fi

bash bin/package.sh
