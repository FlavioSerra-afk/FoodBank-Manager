# Contributing Notes

## Coding standards

Run coding standards only on changed files to avoid legacy noise:

```bash
composer phpcs:changed
```

The helper script resolves the base branch automatically and passes only the modified PHP paths to PHPCS. Fix issues locally (or add inline justifications) before committing.
