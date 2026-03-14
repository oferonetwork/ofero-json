# ofero.json Standard

**ofero.json** is an open, machine-readable metadata standard for organizations, businesses, and protocols. It provides structured identity and business data in a single well-known JSON file at `/.well-known/ofero.json`.

## What is ofero.json?

ofero.json enables:
- **AI/LLM consumption** — automated entity recognition and enrichment
- **Partner integrations** — B2B, web3, fintech onboarding
- **Merchant verification** — legal entity validation
- **Web3 entity metadata** — wallets, tokens, protocols
- **Automatic platform linking** — connect existing social/platform accounts

---

## Quick Start

Place your `ofero.json` at `/.well-known/ofero.json` on your domain:

```json
{
  "language": "en",
  "domain": "example.com",
  "canonicalUrl": "https://example.com/.well-known/ofero.json",
  "metadata": {
    "version": "1.0.0",
    "schemaVersion": "ofero-metadata-1.0",
    "lastUpdated": "2025-01-15T10:00:00Z",
    "createdAt": "2025-01-15T10:00:00Z"
  },
  "organization": {
    "legalName": "Your Company LLC",
    "brandName": { "default": "Your Brand" },
    "entityType": "company",
    "legalForm": "LLC",
    "description": { "default": "What your organization does." },
    "website": "https://example.com",
    "contactEmail": "contact@example.com",
    "contactPhone": "+1234567890",
    "identifiers": {
      "global": {},
      "primaryIncorporation": {
        "country": "US",
        "registrationNumber": "123456",
        "taxId": "12-3456789",
        "vatNumber": ""
      },
      "perCountry": []
    }
  }
}
```

Start from [`docs/examples/minimal.json`](docs/examples/minimal.json) or pick a ready-made example for your business type — see [`docs/examples/`](docs/examples/).

---

## Repository Structure

```
ofero-json-standard/
├── schema/
│   ├── ofero-json-schema.json       # JSON Schema — single source of truth
│   ├── ofero-json-industries.json   # 232-industry taxonomy
│   └── ofero-json-legal-forms.json  # 120+ legal forms database
├── validators/
│   └── ofero-json-validator.ts      # TypeScript/Ajv validator
├── docs/
│   ├── SPECIFICATION.md             # Full field-level specification
│   ├── IMPLEMENTATION.md            # How to extend the standard
│   ├── CHANGELOG.md                 # Version history
│   └── examples/                    # Ready-to-use example files per business type
└── tools/
    ├── wordpress/
    │   ├── ofero-generator/         # WordPress plugin: generate ofero.json via admin UI
    │   └── ofero-shortcodes/        # WordPress plugin: display ofero.json via shortcodes
    ├── php-generator/               # Standalone PHP generator script
    ├── api-registry/                # API endpoint registry tool
    └── ofero-registry/              # Entity registry
```

---

## Validation

The JSON Schema (`schema/ofero-json-schema.json`) is the single source of truth.

### Validation Levels

| Level | What it checks |
|---|---|
| `basic` | Required fields, JSON structure, schema version |
| `moderate` | Basic + email/URL formats, ISO codes *(recommended)* |
| `strict` | Moderate + GPS ranges, hex colors, IBAN, menu item integrity |

### TypeScript

```typescript
import { validateOferoJson } from './validators/ofero-json-validator';

const result = await validateOferoJson(data, 'moderate');
if (result.valid) {
  console.log('Valid!');
  console.log(result.warnings); // recommended fields that are missing
} else {
  console.error(result.errors); // [{ path, message }]
}
```

---

## Multi-Language Support

The base `ofero.json` is written in the company's **primary language** — whichever language the company operates in. There is no requirement to use English. Declare it with the top-level `language` field:

```json
{ "language": "de" }
```

Translations into other languages are embedded inline using the `TranslatableString` structure:

```json
{
  "description": {
    "default": "Eine globale Fintech-Plattform",
    "translations": {
      "en": "A global fintech platform",
      "fr": "Une plateforme fintech mondiale"
    }
  }
}
```

Consumers that need a specific language should read `language` from the file, look up their preferred language in `translations`, and fall back to `default` if not found.

---

## Documentation

- [Examples by business type](docs/examples/) — start here
- [Full Specification](docs/SPECIFICATION.md)
- [Contributor Guide](docs/IMPLEMENTATION.md)
- [Changelog](docs/CHANGELOG.md)

---

## Contributing

See [`docs/IMPLEMENTATION.md`](docs/IMPLEMENTATION.md) for the full checklist of what to update when adding fields to the standard.

---

## License

This repository uses dual licensing:

| Component | License |
|---|---|
| Standard specification, schema, examples, documentation | [MIT](LICENSE) |
| WordPress plugins (`tools/wordpress/`) | GPL-2.0+ (required by WordPress.org) |

The MIT license applies to everything unless a component directory specifies otherwise.
