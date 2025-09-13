# Release Process

Follow these steps to cut a FoodBank Manager release.

1. Build translations.
   ```bash
   composer i18n:build -- --allow-root
   ```
2. Package the plugin.
   ```bash
   bash bin/package.sh
   ```
3. Verify the ZIP.
   ```bash
   unzip -t dist/foodbank-manager.zip
   sha256sum dist/foodbank-manager.zip
   ```
4. Publish a GitHub release (not a prerelease).
5. Attach `dist/foodbank-manager.zip` and `dist/SHA256SUMS.txt` to the release.

We ship pre-built ZIPs on the Releases page because the "Code → Download ZIP" snapshot lacks compiled dependencies and translation files required for WordPress installations.

