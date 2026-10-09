# Examples

Ready-to-use `ofero.json` v2 files for different business types. Pick the one closest to your business, copy it, and edit it with your own data. Every file here validates against [`schema/ofero-json-schema.json`](../../schema/ofero-json-schema.json).

ofero.json describes **who you are** (identity, classification, locations, contact). It is not a catalog: menus, products, services and portfolios live in external feeds that the file points to. See [Why ofero.json is not a catalog](../SPECIFICATION.md#why-oferojson-is-not-a-catalog) and the [v1 → v2 migration guide](../MIGRATION-v1-to-v2.md).

---

## By Business Type

| Business type | Example file | Key sections used |
|---|---|---|
| Minimal (any business) | [`minimal.json`](minimal.json) | organization only |
| Company / SaaS | [`company-full.json`](company-full.json) | businessClassification (worldwide `serviceArea`), locations, platformAccounts, security |
| Remote agency (no venue) | [`audio-agency-example.json`](audio-agency-example.json) | businessClassification.serviceArea, catalog.feeds, no locations |
| Restaurant / Pizzeria | [`restaurant-example.json`](restaurant-example.json) | catalog.feeds (menu), catalog.signature, restaurantDetails, apiEndpoints, promotions |
| Hotel | [`hotel-example.json`](hotel-example.json) | locations, accommodationDetails, restaurantDetails |
| E-commerce store | [`ecommerce-store.json`](ecommerce-store.json) | catalog.feeds (products), featured, `serviceArea` (online + flagship store) |
| Auto service / Mechanic | [`auto-service-example.json`](auto-service-example.json) | catalog.feeds (services), locations, certificates |
| Architecture firm | [`architecture-firm-example.json`](architecture-firm-example.json) | catalog.feeds (services, portfolio), catalog.highlights, team, certificates |
| Modeling agency | [`modeling-agency-example.json`](modeling-agency-example.json) | catalog.feeds (services, portfolio), catalog.highlights, team |
| Karting / Experience | [`karting-example.json`](karting-example.json) | catalog.feeds (packages), catalog.signature, promotions |
| Web3 / DeFi protocol | [`web3-protocol.json`](web3-protocol.json) | wallets, tokenomics, security, verification, roadmap |
| Multi-platform company | [`company-with-both-platforms.json`](company-with-both-platforms.json) | platformAccounts, communications |

---

## Business classification and service area

`businessClassification.industry` is a path from general to specific, using IDs from [`ofero-json-industries.json`](../../schema/ofero-json-industries.json). Companies also need `organization.industry` (usually the first item of that path).

`serviceArea` says where and how you serve customers. Use it when you have no public address, or when you serve people beyond your locations:

```json
"businessClassification": {
  "industry": ["creative-services", "audio-production", "commercial-audio"],
  "primaryProducts": ["radio ad spots", "jingles", "voice-over"],
  "targetMarket": ["B2B"],
  "operationalStatus": "active",
  "serviceArea": {
    "modes": ["remote"],
    "countries": ["US"]
  }
}
```

`modes`: `on-premises` (customers come to you), `at-customer` (you go to them), `remote`. A listed country is served in full unless you narrow it with `regions` (`{ "country": "US", "subdivision": "US-CA" }` or `{ "country": "US", "name": "Austin" }`). Use `"worldwide": true` instead of `countries` if you serve everyone. Full rules: [Service Area](../SPECIFICATION.md#service-area).

---

## Locations

Each location has three independent fields:

- `type` — its role in the company: `headquarters`, `branch`, `international-branch`, `representative-office`
- `facility` (optional) — what kind of space it is: `office`, `store`, `venue`, `workshop`, `warehouse`, `factory`, `distribution-center`
- `publicAccess` (optional) — whether visitors can come: `walk-in`, `by-appointment`, `none` (working space only; `businessHours` are then internal hours)

```json
{
  "id": "downtown",
  "type": "branch",
  "facility": "store",
  "publicAccess": "walk-in",
  "name": "Downtown Store",
  "address": { "street": "1 Main Street", "city": "Springfield", "postalCode": "12345", "country": "US" },
  "businessHours": { "monday": "09:00-18:00", "saturday": "10:00-14:00", "sunday": "Closed", "timezone": "UTC" }
}
```

A business with no public address can leave `locations` out entirely and rely on `serviceArea`.

---

## Catalog — feeds, not inline data

`catalog.feeds[]` points to where your catalog lives. `type` says what the feed contains (`products`, `menu`, `services`, `packages`, `portfolio`, `reservations`, `rooms`, `other`); `format` says how it is encoded. Prefer established formats: Schema.org JSON-LD for menus and products, Google Merchant XML for e-commerce.

```json
"catalog": {
  "defaultCurrency": "USD",
  "feeds": [
    {
      "type": "menu",
      "format": "schema.org-jsonld",
      "standard": "schema.org/Menu",
      "url": "https://example.com/feeds/menu.jsonld",
      "name": { "default": "Main menu" },
      "lastUpdated": "2026-05-22T10:00:00Z"
    }
  ]
}
```

Only small previews stay inline:

- `catalog.signature[]` — up to 6 hero items (signature dishes, best-selling packages)
- `catalog.highlights[]` — up to 6 portfolio teasers
- `featured.products[]` / `featured.services[]` — up to 12 each

```json
"signature": [
  {
    "id": "margherita",
    "name": { "default": "Margherita Pizza" },
    "description": { "default": "San Marzano tomato, fior di latte, fresh basil" }
  }
]
```

How each example uses this:

- **Restaurant:** main menu as a Schema.org Menu JSON-LD feed, daily specials as a second `menu` feed, signature dishes inline, seating/cuisine/delivery details in `restaurantDetails`.
- **Auto service:** a `services` feed, plus `priceListUrl` for a printable price list.
- **E-commerce:** a `products` feed. On WordPress + WooCommerce, the Ofero Generator plugin serves one automatically at `/wp-json/ofero/v2/products`.
- **Architecture / modeling agency:** `services` and `portfolio` feeds, with project teasers in `highlights`.
- **Karting:** a `packages` feed, with the best-selling packages in `signature`.

Field-by-field reference: [`catalog.feeds[]` reference](../SPECIFICATION.md#catalogfeeds-reference).

---

## Translations

Translatable fields (`brandName`, `description`, `tagline`, `keywords`, catalog names…) are always objects, even with a single language:

```json
"brandName": { "default": "Example", "translations": { "fr": "Exemple" } }
```

List the languages you provide in `availableLanguages`.

---

## Web3 / Protocol

Use `wallets`, `tokenomics`, `verification`, `security` and `roadmap`. See [`web3-protocol.json`](web3-protocol.json) for wallet ownership proofs, token metadata, distribution and DNS verification.

---

## Tools

### WordPress
Copy `tools/wordpress/ofero-generator/` into `wp-content/plugins/`, activate it, and use the admin UI. Select your business type — only relevant sections appear.

### PHP (custom site)
Use `tools/php-generator/ofero-generator.php` as a starting point for generating the file programmatically.

### Manual
Copy the closest example, edit the values, and validate against the schema with any JSON Schema (draft 2020-12) validator using `schema/ofero-json-schema.json`, or with the TypeScript validator:

```typescript
import { validateOferoJson } from '../../validators/ofero-json-validator';
const result = await validateOferoJson(data, 'moderate');
```
