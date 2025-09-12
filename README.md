Docs-Revision: 2025-09-04 (Wave v1.1.4 – Final)
# FoodBank Manager Plugin

See [Docs/PRD-foodbank-manager.md](../Docs/PRD-foodbank-manager.md) for the full product requirements and specifications.

## CLI

FoodBank Manager exposes a parent command via `WP_CLI::add_command()`. After
activation you can run:

```bash
wp fbm version
```
