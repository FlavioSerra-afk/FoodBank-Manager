# Manual Release

1) Choose the bump:
   - patch: `composer ver:bump`
   - minor: `composer ver:bump:minor`
   - major: `composer ver:bump:major`

   This updates: includes/Core/Plugin.php (FBM_VER), foodbank-manager.php,
   composer.json, readme.txt, CHANGELOG.md (header created by the bump script).

2) Verify:

```
composer install --no-interaction --no-progress
composer lint
composer phpcs:lanes -- --report=json --report-file=build/phpcs-lanes.json
FBM_PHPSTAN=1 composer phpstan:fast && FBM_PHPSTAN=1 composer phpstan
vendor/bin/phpunit --testsuite Unit --testdox
bash bin/package.sh
```

3) Tag and push:

```
git tag v$(php -r "require 'includes/Core/Plugin.php'; echo \FoodBankManager\Core\Plugin::FBM_VER;")
git push origin --tags
```

4) (Optional) Run **Manual Build (Package Only)** in Actions to produce a downloadable ZIP.

5) Publish the GitHub release manually and attach `dist/foodbank-manager.zip`.
