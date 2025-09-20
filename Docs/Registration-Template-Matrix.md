# Registration Template Matrix

## Allowed HTML and Attributes

The registration editor sanitizes stored markup using a fixed allow-list. The following elements are preserved along with the listed attributes:

| Element | Allowed attributes |
| --- | --- |
| `div`, `section`, `article`, `p`, `span`, `strong`, `em`, `small`, `sup`, `sub`, `hr`, `dl`, `dt`, `dd` | `class`, `id`, `role`, `title`, any `aria-*`, any safe `data-*` |
| Headings `h1`–`h6` | `class`, `id`, `role`, `title`, any `aria-*`, any safe `data-*` |
| Lists `ul`, `ol`, `li` | `class`, `id`, `role`, `title`, any `aria-*`, any safe `data-*` |
| `fieldset`, `legend` | `class`, `id`, `role`, `title`, any `aria-*`, any safe `data-*` |
| `label` | `class`, `id`, `role`, `title`, any `aria-*`, any safe `data-*`, `for` |
| `a` | `class`, `id`, `role`, `title`, any `aria-*`, any safe `data-*`, `href`, `target`, `rel` |
| `br` | *(no attributes)* |

All other HTML is stripped during save. Inline event handlers, scripts, and unknown attributes are never persisted.

## Supported Template Tags

The editor accepts CF7-style tags for rendering inputs. Supported tag types and attributes are listed below.

| Tag type | Supported attributes |
| --- | --- |
| `text`, `email`, `tel`, `date`, `number`, `textarea` | `*` (required flag), `id:`, `class:`, `placeholder`, `autocomplete`, `min:`, `max:`, `step:` |
| `radio`, `checkbox`, `select` | `*`, `id:`, `class:`, `use_label_element`, `multiple` (`select` only), `placeholder` (single-value fallbacks) |
| `file` | `id:`, `class:` |
| `submit` | `id:`, `class:` |

Additional notes:

- Field names map directly to stored submission keys. Canonical names (`fbm_first_name`, `fbm_last_initial`, `fbm_email`, `fbm_household_size`, `fbm_registration_consent`) feed the member record.
- Numeric inputs honour `min:`, `max:`, and `step:` values. Server-side clamps also enforce a maximum household size and reject negative values.
- File uploads remain single-file only and are validated against the configured MIME/type limits.
- Conditional visibility rules operate on the sanitized field catalog produced by these tags.
