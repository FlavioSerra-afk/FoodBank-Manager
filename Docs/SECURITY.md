# Security Overview — v1.3.0

FoodBank Manager encrypts selected personally identifiable information (PII) at rest using a versioned envelope scheme introduced in v1.3.0.

## Envelope Encryption

* **Cipher:** AES-256-GCM with per-record, randomly generated data-encryption keys (DEKs).
* **Master key:** Derived at runtime via `HKDF-SHA256(AUTH_KEY || SECURE_AUTH_KEY, salt = site_url(), info = "fbm-kms-v1", len = 32)`.
* **Envelope structure:**
  ```json
  {
    "v": "1",
    "alg": "AES-256-GCM",
    "dek": "<base64-wrapped-dek>",
    "nonce": "<base64-nonce>",
    "tag": "<base64-tag>",
    "ct": "<base64-ciphertext>",
    "aad": ""
  }
  ```
* **Authenticated data:** `site_url()|fbm|v1|{table}|{column}|{record_id}` ties each envelope to its logical location.
* **Wrapped DEK:** Stored alongside ciphertext using AES-256-GCM with a master-key-derived AAD suffix (`|dek`).

## Scope

* `fbm_members.first_name`
* `fbm_members.last_initial`
* `fbm_mail_failures[].email`

Backwards compatibility is preserved. Plaintext values are migrated on demand and decrypted transparently when encountered.

## Rotation & Tooling

* **Admin Diagnostics:** Managers can migrate or rotate adapters from the Diagnostics page (nonce-protected, capability checked) with dry-run support and resumable progress.
* **WP-CLI:** `wp fbm crypto status|migrate|rotate|verify [--adapter=<id>] [--limit=<n>] [--dry-run]` mirrors admin capabilities for batch automation.
* **Progress tracking:** Adapter-specific checkpoints persist under `fbm_encryption_progress_*` options to support resumable batches. Completion clears stored progress.

## Settings

* The "Encrypt new writes" toggle is available from the Settings page. Fresh installs enable encryption automatically; existing sites can opt-in before migrating legacy plaintext. When enabled, new member names and diagnostics mail addresses are stored as envelopes immediately.

## Additional Notes

* QR tokens remain opaque HMAC-signed strings; their external format is unchanged.
* Constant-time comparisons for token verification continue to use `hash_equals`.
