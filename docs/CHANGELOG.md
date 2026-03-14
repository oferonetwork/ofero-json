# Changelog

All notable changes to the ofero.json metadata standard will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2026-03-14

### Changed

- **Primary language is no longer required to be English.** The `default` field in every `TranslatableString` must now match the language declared by the top-level `language` field — which can be any ISO 639-1 language code. Companies should write their `ofero.json` in whatever language they operate in.
- Removed language overlay files (`ofero-{lang}.json`) from the specification. Translations are handled entirely via the inline `TranslatableString` structure (`default` + `translations` object), making separate overlay files redundant.
- Updated `availableLanguages` schema description to reflect inline-only translation model.
- Updated all documentation examples to use `de`/`fr` instead of `ro` as the non-English example language.

### Removed

- Language overlay file pattern (`/.well-known/ofero-{lang}.json`) — superseded by inline `TranslatableString.translations`.

---

## [1.0.0] - 2025-03-07

### Added

#### Schema and Structure
- Initial public release of ofero.json metadata standard
- Complete JSON Schema (Draft 2020-12) specification
- 45+ interface definitions covering all entity types
- Schema version: `ofero-metadata-1.0`
- File version: `1.0`
- **`specialHours` field in `Location`** — Optional array of `SpecialHoursEntry` objects that override `businessHours` for a specific date (e.g., `"date": "2025-12-25"`) or a date range (e.g., `"from"`/`"to"`). Hours use the same `HH:MM-HH:MM` or `"Closed"` format. See [Locations Section](SPECIFICATION.md#special-hours-specialhours) for full details.

#### Entity Types
- Support for 9 entity types:
  - `company` - For-profit businesses
  - `foundation` - Charitable foundations
  - `association` - Member associations
  - `ngo` - Non-governmental organizations
  - `protocol` - Web3 protocols
  - `store` - Online/offline stores
  - `individual` - Personal brands
  - `project` - Open source projects
  - `other` - Custom types

#### Legal Forms
- Legal forms database with 120+ forms
- Coverage for 13+ countries (US, GB, DE, FR, NL, CA, AU, CH, ES, IT, RO, SE, INTL)
- 10 legal form categories:
  - nonprofit
  - foundation
  - ngo
  - cooperative
  - social_enterprise
  - limited_liability
  - joint_stock
  - partnership
  - sole_proprietor
  - custom

#### Core Sections
- **Organization** - Legal entity information and identifiers
- **Locations** - Physical locations with business hours and contacts
- **Banking** - Bank account information
- **Wallets** - Blockchain wallet addresses with extensions
- **Brand Assets** - Logos, icons, color palettes, brand guidelines
- **Catalog** - Product and service feeds
- **Featured** - Featured products and services
- **Verification** - Domain and wallet ownership proofs
- **Security** - Security contact and PGP information
- **Communications** - Social media, messaging, support channels
- **AI Settings** - AI indexing preferences
- **Compliance** - Regulatory compliance and policies
- **Extensions** - Schema version and notes

#### Optional Sections
- **Recommendations** - Recommended websites and resources
- **Certificates** - Certifications and accreditations
- **Promo Codes** - Promotional offers
- **Team** - Leadership, advisors, investors
- **Tokenomics** - Token and cryptocurrency information
- **APIs** - Public API endpoints
- **Integrations** - Partner integrations and webhooks
- **Mutual Partnerships** - Verified partnerships
- **Analytics** - Public statistics
- **Roadmap** - Project milestones
- **Press** - Press kit and news
- **Careers** - Job openings and culture

#### Multi-Language Support
- Language overlay system for translations
- Clearly defined translatable vs non-translatable fields
- Support for ISO 639-1 language codes
- Base file + overlay pattern (`ofero.json` + `ofero-{lang}.json`)

#### Blockchain/Web3 Features
- Wallet address support with blockchain-specific extensions
- EVM extensions (chainId, explorerUrl)
- DEX and liquidity pool metadata
- Chainlink oracle feed information
- Token and tokenomics section
- Wallet ownership verification (cryptographic proofs)
- Support for multiple blockchains (MultiversX, Ethereum, Solana, etc.)

#### Validation
- Three validation levels (basic, moderate, strict)
- Format validation (email, URI, date-time, date)
- Pattern validation (ISO codes, hex colors, coordinates)
- Enum constraints for entity types, categories, statuses
- Number range validation
- Required field enforcement
- Schema-based validation using JSON Schema

#### Identifiers
- Global identifiers (LEI, DUNS)
- Primary incorporation identifiers
- Country-specific identifier support
- ISO 3166-1 alpha-2 country codes
- ISO 4217 currency codes
- ISO 639-1 language codes

#### Documentation
- Complete specification document (SPECIFICATION.md)
- Quick start guide (README.md)
- Changelog (CHANGELOG.md)
- Reference examples for all entity types
- Field-level descriptions for all properties

#### Examples
- Full example with all sections
- Company example
- Association example
- Protocol example
- Romanian language overlay example

#### Restaurant / Food Service
- **`MenuItem.portionSize`** — Portion size or weight string (e.g., `"220g"`, `"1.3kg"`, `"2-3 persons"`)
- **`MenuItem.ingredients`** — Array of ingredient strings (e.g., `["eggs", "bacon", "toast"]`)
- **`MenuItem.priceUnit`** — Optional string for non-standard pricing basis (e.g., `"per glass"`, `"per kg"`). Omit for standard per-portion pricing. Currency is always defined at `catalog.defaultCurrency` — do not use a `priceFormatted` field.
- **`MenuItem.variants`** — Size or format variants, each with a required `id`, `name`, and `price`
- **`MenuItem.addons`** — Optional add-ons, each with a required `id`, `name`, and `price`
- **`MenuCategory.serviceHours`** — Hours when the category is served, format `HH:MM-HH:MM` (e.g., `"08:00-12:00"`). Omit if available all day.
- **`catalog.dailyMenu`** — Weekly rotating daily specials, independent of the regular menu. Each item is standalone (not a reference to `catalog.menu`) to allow different portion sizes and prices. Supports:
  - `weekOf` — The Monday date of the applicable week (`YYYY-MM-DD`)
  - `note` — Translatable note (e.g., "Menu changes every Monday")
  - `schedule` — Object keyed by day (`monday`–`sunday`), each an array of `DailyMenuItem`
- **`DailyMenuItem`** — Schema definition for daily menu items with: `name`, `description`, `portionSize`, `price`, `ingredients`, `course`, `dietary`, `allergens`, `available`
  - `course` enum: `starter`, `soup`, `main`, `dessert`, `drink`, `side`, `other`

### Technical Details

#### File Location
- Standard location: `/.well-known/ofero.json`
- Language overlays: `/.well-known/ofero-{lang}.json`
- HTTPS required for verification
- CORS headers recommended

#### Format
- JSON (UTF-8 encoding)
- MIME type: `application/json`
- Recommended max size: < 500 KB
- Cache-Control: `public, max-age=3600` (recommended)

#### Schema
- JSON Schema Draft 2020-12
- Schema ID: `https://ofero.network/schemas/ofero-json-schema.json`
- Uses `$defs` for reusable components
- Comprehensive field descriptions
- Validation rules built into schema

### Best Practices Established

1. Keep information current (quarterly review recommended)
2. Use HTTPS for all deployments
3. Validate before deploying
4. Start with minimal required fields
5. Use wallet proofs and DNS records for verification
6. Consistent naming across all sections
7. Absolute URLs for all external references
8. Appropriate cache headers (1-24 hours)
9. Regular audits and updates
10. Never include secrets or credentials

### Security Considerations

- Wallet ownership verification via cryptographic signatures
- DNS verification via TXT records
- PGP public key support
- Responsible disclosure URL
- Security contact email
- No private keys or credentials in file
- Public contact information only

### Known Limitations

- No automated notification system for updates
- No centralized registry of ofero.json files
- No built-in versioning for file updates
- Relies on manual updates by organizations

### Breaking Changes

None (initial release)

### Deprecated

None (initial release)

### Removed

None (initial release)

### Fixed

None (initial release)

### Security

None (initial release)

---

## Version Format

Version numbers follow [Semantic Versioning](https://semver.org/):
- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality additions
- **PATCH** version for backwards-compatible bug fixes

Schema Version Format:
- `ofero-metadata-MAJOR.MINOR` (e.g., `ofero-metadata-1.0`)

---

## Links

- **Specification**: [SPECIFICATION.md](SPECIFICATION.md)
- **Quick Start**: [README.md](README.md)
- **JSON Schema**: [ofero-json-schema.json](../schema/ofero-json-schema.json)
- **Legal Forms**: [ofero-json-legal-forms.json](../schema/ofero-json-legal-forms.json)
- **Examples**: [examples/](examples/)

---

[1.0.0]: https://github.com/oferonetwork/ofero-json/releases/tag/v1.0.0

