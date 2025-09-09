Docs-Revision: 2025-09-09 (Wave RC3 Fix Pack)
# Install and Setup

Verify the distribution ZIP before installing:
```bash
zipinfo -1 foodbank-manager.zip | head -n1
unzip -l foodbank-manager.zip | grep 'foodbank-manager/foodbank-manager.php'
```
The first entry must be `foodbank-manager/` and the ZIP must contain `foodbank-manager/foodbank-manager.php`.

.mo files compile automatically during packaging when `msgfmt` is available.
