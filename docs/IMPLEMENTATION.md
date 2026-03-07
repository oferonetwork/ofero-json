# Contributing to the Ofero.json Standard

This document describes how to extend or modify the ofero.json standard — adding new fields, sections, or validation rules.

---

## Architecture

There is **no code generation** in this repository. The source of truth is the JSON Schema, and everything else must be updated manually and kept in sync.

```
schema/ofero-json-schema.json          ← SOURCE OF TRUTH — edit this first
validators/ofero-json-validator.ts     ← custom semantic validation — update manually
docs/SPECIFICATION.md                  ← public specification — update manually
docs/examples/                         ← usage examples — update manually
tools/wordpress/                       ← WordPress plugin — update manually
tools/php-generator/                   ← PHP generator — update manually
```

---

## Checklist: Adding a New Field

When adding a field to the standard, every item in this checklist must be completed in order.

### 1. Schema — `schema/ofero-json-schema.json`

This is always the first step. Add the field to the correct `$defs` block with:
- `type`
- `description` in English
- `enum` values if applicable
- `format` if applicable (e.g., `date`, `uri`)

```json
"newField": {
  "type": "string",
  "description": "What this field represents and when to use it"
}
```

For `TranslatableString` fields (names, descriptions shown to users):
```json
"newField": {
  "$ref": "#/$defs/TranslatableString",
  "description": "..."
}
```

---

### 2. Validator — `validators/ofero-json-validator.ts`

Ajv validates structure automatically from the schema. Add manual logic here only for rules the schema cannot express:

- **Format patterns** not covered by JSON Schema (e.g., `HH:MM-HH:MM` for time ranges)
- **Cross-field consistency** (e.g., domain must match canonicalUrl)
- **Semantic rules** (e.g., price must be non-negative even for optional fields)
- **Warnings** for recommended but missing fields

Add errors to the correct function:
- `validateBasic()` — required fields, structural integrity
- `validateModerate()` — format consistency, cross-field rules
- `validateStrict()` — value ranges, semantic correctness
- `getRecommendedFieldWarnings()` — optional but recommended fields

If the schema already enforces the rule via `type`, `enum`, `format`, `minimum`, `pattern`, etc. — **do not duplicate it** in the validator.

---

### 3. Specification — `docs/SPECIFICATION.md`

Add the field to the Field Reference section. Include:
- Field name, type, and whether it is required or optional
- Description
- Example value
- Which entity types it applies to (if not universal)

---

### 4. Examples — `docs/examples/`

Update the relevant example file(s) to demonstrate the new field with realistic data in English.

- `restaurant-example.json` — for food service fields (`menu`, `dailyMenu`, `restaurantDetails`)
- `hotel-example.json` — for accommodation fields
- `company-full.json` — for general business fields
- Add a new example file only if no existing example covers the use case

---

### 5. WordPress Generator — `tools/wordpress/ofero-generator/`

If the field should appear in the WordPress admin UI for generating ofero.json files:

- Add the input field to the relevant template in `templates/sections/`
- Handle it in `includes/class-form-handler.php`

The section templates map to schema sections:

| Template | Schema section |
|---|---|
| `templates/sections/basic.php` | `organization`, `metadata` |
| `templates/sections/restaurant.php` | `restaurantDetails` |
| `templates/sections/menu.php` | `catalog.menu`, `catalog.dailyMenu` |
| `templates/sections/locations.php` | `locations` |
| `templates/sections/branding.php` | `branding` |
| `templates/sections/communications.php` | `communications` |
| `templates/sections/banking.php` | `banking` |
| `templates/sections/rooms.php` | `rooms` (hotel) |

---

### 6. PHP Generator — `tools/php-generator/`

If the field should be supported by the PHP generator template:

- Update `oferoconfig-TEMPLATE.json` with the new field
- Update `ofero-generator.php` to handle and output the field

---

## Validation Levels Reference

| Level | What it checks |
|---|---|
| `basic` | Required top-level fields, metadata version, schema version |
| `moderate` | Email/URL formats, ISO country and language codes, domain consistency |
| `strict` | GPS coordinate ranges, hex color format, IBAN length, `serviceHours` format, `dailyMenu` item integrity, menu item values |

Warnings (not errors) are generated at `moderate` and `strict` for:
- Missing `ingredients` on menu items
- Missing `portionSize` on menu items
- Missing recommended sections (`verification`, `security`, `communications`, `ai`)
- HTTP instead of HTTPS on website URL

---

## Language Rules

All field names, `description` values in the schema, error messages in the validator, and content in example files must be in **English**. This is a hard rule for the standard.

`TranslatableString` fields in example JSON files use only `"default"` (no `"translations"`) to keep examples readable.

---

## Naming Conventions

- Field names: `camelCase`
- Enum values: `kebab-case` (e.g., `"gluten-free"`, `"dairy-free"`)
- IDs in examples: `kebab-case` (e.g., `"bruschetta"`, `"main-course"`)
- Dates: ISO 8601 (`YYYY-MM-DD` or `YYYY-MM-DDTHH:MM:SSZ`)
- Time ranges: `HH:MM-HH:MM` (e.g., `"08:00-12:00"`)
- Weights/sizes: string with unit (e.g., `"220g"`, `"1.3kg"`, `"350ml"`)
