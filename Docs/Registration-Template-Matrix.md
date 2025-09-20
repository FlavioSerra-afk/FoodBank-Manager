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

## Conditional Visibility Groups

Conditional logic now supports grouped rules with AND/OR operators, multiple conditions, and more than one action per group. Rules always operate on the sanitized field catalog parsed from the template. The UI and REST payload share the same structure:

```json
{
  "enabled": true,
  "groups": [
    {
      "operator": "and",
      "conditions": [
        {"field": "fbm_first_name", "operator": "equals", "value": "yes"}
      ],
      "actions": [
        {"type": "show", "target": "fbm_extra_question"}
      ]
    }
  ]
}
```

### Supported operators

| Operator | Description |
| --- | --- |
| `equals` / `not_equals` | Case-insensitive string comparison against the normalized field value. |
| `contains` | Matches when the value (or any multi-select option) contains the provided substring. |
| `empty` / `not_empty` | Checks whether the field has any non-empty input. |
| `lt`, `lte`, `gt`, `gte` | Numeric or date comparisons. For dates the comparison uses ISO-8601 parsing (e.g. `2024-12-31`). |

### Supported actions

| Action | Effect |
| --- | --- |
| `show` | Hides the target by default and reveals it when the group matches. |
| `hide` | Hides the target whenever the group matches. |
| `require` | Marks the target as required while the group matches (server-side validation enforces it). |
| `optional` | Clears the required flag while the group matches, even if the template marks the field as required. |

Groups are evaluated in the stored order. When multiple groups target the same field, the last matching action wins. The public form and server use the same evaluator to keep behaviour consistent, and hidden fields are ignored on submit.

### Examples

1. **Show file upload when proof required**

```json
{
  "operator": "and",
  "conditions": [
    {"field": "fbm_proof_required", "operator": "equals", "value": "yes"}
  ],
  "actions": [
    {"type": "show", "target": "fbm_proof_of_address"}
  ]
}
```

2. **Require pickup date when has children = yes**

```json
{
  "operator": "and",
  "conditions": [
    {"field": "fbm_has_children", "operator": "equals", "value": "yes"}
  ],
  "actions": [
    {"type": "show", "target": "fbm_preferred_date"},
    {"type": "require", "target": "fbm_preferred_date"}
  ]
}
```

3. **Multi-action with conflict resolution**

```json
[
  {
    "operator": "and",
    "conditions": [
      {"field": "fbm_status", "operator": "equals", "value": "paused"}
    ],
    "actions": [
      {"type": "show", "target": "fbm_pause_reason"}
    ]
  },
  {
    "operator": "and",
    "conditions": [
      {"field": "fbm_status", "operator": "equals", "value": "paused"}
    ],
    "actions": [
      {"type": "optional", "target": "fbm_pause_reason"}
    ]
  }
]
```

The second group runs after the first, so the field stays visible but the optional action overrides any previous requirement.

## Import/Export & Presets

Exports generated from the editor include the current rule schema version, a normalized field catalogue, and the sanitized condition groups. The payload mirrors the REST preview response used during imports:

```json
{
  "schema": {
    "version": 1,
    "generated_at": "2024-05-15T12:00:00Z"
  },
  "fields": [
    {"name": "fbm_first_name", "label": "First name", "type": "text"},
    {"name": "fbm_household_size", "label": "Household size", "type": "number"}
  ],
  "conditions": {
    "enabled": true,
    "groups": [
      {
        "operator": "and",
        "conditions": [
          {"field": "fbm_household_size", "operator": "gt", "value": "4"}
        ],
        "actions": [
          {"type": "show", "target": "fbm_children_ages"},
          {"type": "optional", "target": "fbm_children_ages"}
        ]
      }
    ]
  }
}
```

- **Schema version** – Import checks `schema.version` against the editor's `Conditions::SCHEMA_VERSION`. Mismatched versions are rejected before any mapping occurs.
- **Field catalogue** – The importer creates suggested mappings by comparing incoming `name` and `label` values with the current template. Administrators can auto-map from these suggestions or adjust manually.
- **Group analysis** – The preview response highlights groups with missing field mappings so they can be corrected before committing. Unmapped conditions or actions cause the affected group to be skipped during the server-side import step.
- **Import flow** – JSON is validated and sanitized on the server. Only mapped groups with at least one condition and one action are retained. The import action updates `fbm_registration_settings['conditions']` and reports the number of groups skipped.

### Import diff viewer

The import modal now renders a side-by-side diff before any rules are saved:

- **Incoming JSON** – The left panel shows the sanitized payload from the export, preserving group order so administrators can confirm intent.
- **Resolved mapping** – The right panel displays the result of applying the chosen mappings to the current field catalogue. Missing fields are highlighted in the summary list below the diff.
- **Summary** – Separate lists detail which groups will import and which will be skipped, including reasons such as `missing_field` or `empty`. Skipped groups never reach the settings option.

Diff generation uses the new `/registration/editor/conditions/diff` endpoint which enforces the REST nonce and `fbm_manage` capability. The modal keeps the **Apply import** button disabled until at least one group can be imported, ensuring administrators acknowledge the diff output before committing changes.

### Guided presets

The editor exposes a curated set of six presets sourced from `TemplateDefaults::presets()`. Each preset ships with:

- A stable identifier, label, and description shown in the presets menu.
- Placeholder tokens for fields or comparison values (e.g. `{{childrenCountField}}`). The modal prompts the administrator to select real fields or provide values before insertion.
- One or more sanitized groups that are merged into the existing rule set when applied. Placeholders are replaced with the selected mappings before the groups are appended.

Presets are JSON-backed and version-agnostic so the catalogue can grow without touching editor logic. Administrators can edit the inserted groups before saving, and the aria-live announcer confirms when a preset has been added.
