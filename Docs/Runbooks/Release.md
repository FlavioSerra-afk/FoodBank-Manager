# Release Runbook

Follow these steps to prepare and ship a FoodBank Manager release.

## 1. Verify workspace
- Ensure `git status` is clean or only contains intentional release changes.
- Run the full QA suite:
  ```bash
  composer phpcs
  composer phpstan
  composer test
  ```
- Confirm the package builds cleanly with the local vendor cache:
  ```bash
  FBM_PACKAGE_USE_LOCAL_VENDOR=1 bash bin/package.sh
  ```
  Review `dist/foodbank-manager-manifest.txt` if you need a quick file inventory.

## 2. Align versions
- Bump the plugin version using the helper, e.g.:
  ```bash
  composer ver:bump:patch
  ```
- Re-run `composer test` to ensure packaging and version alignment tests now pass with the new version.

## 3. Update changelog
- Generate (or confirm) the current version heading in `CHANGELOG.md`:
  ```bash
  composer changelog:release
  ```
- Add descriptive notes under the new heading. Leave the `[Unreleased]` section for upcoming work.

## 4. Build the distributable
- Produce the final ZIP and manifest:
  ```bash
  composer build:zip
  ```
- Verify the first entry inside `dist/foodbank-manager.zip` is `foodbank-manager/` and that the manifest matches the archive contents (the packaging test enforces this).

## 5. Final checks
- Inspect `dist/foodbank-manager.zip` manually if needed.
- Stage and commit the version bump, changelog updates, and manifest if part of the release.
- Tag the release in git and push tags to GitHub.
- Create the GitHub release with the changelog excerpt and attach `dist/foodbank-manager.zip`.

The Composer meta-task `composer release:prep` runs the changelog preparation and packaging steps in sequence once versions are aligned.
