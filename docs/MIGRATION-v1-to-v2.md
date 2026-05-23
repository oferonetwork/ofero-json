# Migrating from ofero.json v1.x to v2.0

**Status:** Required migration. v1 files (`schemaVersion: "ofero-metadata-1.0"`) are rejected by v2 validators on purpose.

This guide explains what changed, why it changed, and how to upgrade your ofero.json file.

---

## Why we made this breaking change

Two production problems forced the redesign:

1. **CMS plugin meltdown.** Real ofero.json files reached 2 MB once businesses added their full menu or product catalog inline. The reference WordPress shortcodes plugin parsed the entire JSON tree on every shortcode render. Inside Elementor, this crashed pages — even though most shortcodes only need `organization`, `locations`, or `communications.social`. The download cost is paid once; the **parse cost is paid every render**.
2. **Wrong separation of concerns.** Identity/metadata changes rarely and is owned by one team. Catalog data (menus, prices, stock, daily specials) changes constantly and is owned by other systems (POS, inventory, e-commerce). Coupling them in one file forces every consumer to re-download the slow-changing identity data every time the fast-changing catalog ticks.

The fix is to make ofero.json an **identity/metadata file only**. Operational catalogs live in external feeds referenced by URL, preferably using existing industry standards (Schema.org Menu/Product, Google Merchant XML, JSON Feed).

See also: [Why ofero.json is not a catalog](SPECIFICATION.md#why-oferojson-is-not-a-catalog) in the v2.0 specification.

---

## What changed at a glance

| Aspect | v1.x | v2.0 |
| --- | --- | --- |
| `metadata.schemaVersion` | `"ofero-metadata-1.0"` | `"ofero-metadata-2.0"` |
| Recommended max file size | 500 KB | **100 KB** |
| Inline menus, products, services, packages, portfolios | Allowed | **Removed from schema** |
| External feed references | `catalog.productFeeds[]`, `catalog.serviceFeeds[]` (two separate arrays) | `catalog.feeds[]` (one unified array with `type` enum) |
| Inline previews for landing pages | Implicit (whole catalog inline) | Explicit, hard-capped: `catalog.signature[]` (≤ 6), `catalog.highlights[]` (≤ 6), `featured.products[]` (≤ 12), `featured.services[]` (≤ 12) |

---

## Field-by-field mapping

| v1 inline field | v2 replacement |
| --- | --- |
| `catalog.menu` (full menu with categories, items, variants, addons) | `catalog.feeds[]` with `type: "menu"`, `format: "schema.org-jsonld"`, `standard: "schema.org/Menu"` |
| `catalog.dailyMenu` (weekly schedule of specials) | `catalog.feeds[]` with `type: "menu"`, plus a small `catalog.signature[]` for the day's hero items |
| `catalog.services[]` | `catalog.feeds[]` with `type: "services"` |
| `catalog.packages[]` | `catalog.feeds[]` with `type: "packages"` |
| `catalog.portfolio[]` | `catalog.feeds[]` with `type: "portfolio"` plus `catalog.highlights[]` (≤ 6) for hero items |
| `catalog.productFeeds[]` | `catalog.feeds[]` with `type: "products"` (merge entries) |
| `catalog.serviceFeeds[]` | `catalog.feeds[]` with `type: "services"` (merge entries) |
| `catalog.defaultCurrency` | Unchanged |
| `catalog.priceListUrl` | Unchanged |
| `featured.products[]`, `featured.services[]` | Unchanged shape, now **capped at 12 items each** |
| `restaurantDetails`, `accommodationDetails` | Unchanged (these are operational metadata, not catalog) |

---

## Step-by-step migration recipe

### 1. Export your inline catalog to an external feed

Pick the right standard for your data:

- **Restaurant menu** → publish a [Schema.org Menu](https://schema.org/Menu) JSON-LD file at e.g. `https://yourdomain.com/feeds/menu.jsonld`
- **E-commerce products** → publish a [Google Merchant Feed](https://support.google.com/merchants/answer/7052112) XML, or [Schema.org Product](https://schema.org/Product) JSON-LD
- **Services / packages** → publish JSON Feed 1.1 or your own JSON; declare `format: "json"`
- **Portfolio** → publish JSON or JSON-LD with [Schema.org CreativeWork](https://schema.org/CreativeWork)

Host the feed at a stable HTTPS URL. Serve `Last-Modified` and `ETag` headers so consumers can do conditional GETs.

### 2. Replace inline catalog blocks with `catalog.feeds[]` entries

Before (v1):

```json
{
	"catalog": {
		"defaultCurrency": "USD",
		"menu": {
			"categories": [
				{ "id": "pizza", "items": [ /* 80 menu items */ ] }
			]
		}
	}
}
```

After (v2):

```json
{
	"catalog": {
		"defaultCurrency": "USD",
		"feeds": [
			{
				"type": "menu",
				"format": "schema.org-jsonld",
				"standard": "schema.org/Menu",
				"url": "https://restaurant.example.com/feeds/menu.jsonld",
				"name": { "default": "Main menu" },
				"language": "en",
				"lastUpdated": "2026-05-22T10:00:00Z",
				"itemCount": 80
			}
		],
		"signature": [
			{
				"id": "margherita",
				"name": { "default": "Pizza Margherita" },
				"category": "pizza",
				"priceFormatted": "$14.00",
				"imageUrl": "https://restaurant.example.com/img/margherita.jpg"
			}
		]
	}
}
```

### 3. Bump `metadata.schemaVersion` and `metadata.version`

```json
{
	"metadata": {
		"version": "2.0.0",
		"schemaVersion": "ofero-metadata-2.0",
		"lastUpdated": "2026-05-22T10:00:00Z"
	}
}
```

Bumping `metadata.version` to a new MAJOR (e.g. `2.0.0`) signals to consumers that the file's shape changed. This is independent of the schema version bump.

### 4. Trim `featured[]` to ≤ 12 items per category

If you previously had 50 featured products, pick the 12 most representative. The rest belong in your products feed.

### 5. Validate

Run the v2 validator against your updated file:

```bash
npx tsx validators/ofero-json-validator.ts your-ofero.json strict
```

A v2 validator will reject the file with a clear error if any v1 catalog field is still present (`catalog.menu`, `catalog.services`, etc.).

### 6. Update the file size budget

Aim for **< 100 KB**. If you're over, you probably still have data that belongs in a feed (e.g. a 200-person team roster, a 50-entry press archive).

---

## How to host a Schema.org Menu JSON-LD endpoint

The simplest case: a static JSON-LD file served by your web server.

```jsonld
{
	"@context": "https://schema.org",
	"@type": "Menu",
	"name": "Main menu",
	"inLanguage": "en",
	"hasMenuSection": [
		{
			"@type": "MenuSection",
			"name": "Pizza",
			"hasMenuItem": [
				{
					"@type": "MenuItem",
					"name": "Pizza Margherita",
					"description": "Tomato sauce, mozzarella, basil",
					"offers": {
						"@type": "Offer",
						"price": "14.00",
						"priceCurrency": "USD"
					},
					"suitableForDiet": "https://schema.org/VegetarianDiet"
				}
			]
		}
	]
}
```

Serve it at any stable HTTPS URL and reference it from `catalog.feeds[]`. If your menu changes frequently, regenerate the file from your POS and update the `lastUpdated` timestamp on the corresponding feed entry in ofero.json.

---

## FAQ

**Q: Can I keep my v1 file and just bump the schemaVersion?**
No. v2 validators reject any of the v1 catalog fields (`menu`, `dailyMenu`, `services`, `packages`, `portfolio`, `productFeeds`, `serviceFeeds`). You must move catalog data to external feeds.

**Q: What if I don't want to host external feeds?**
You can keep up to 6 signature items + 6 highlights + 12 featured products + 12 featured services inline. If your full catalog has more items than that, you need a feed — there is no inline escape hatch.

**Q: My catalog is small (5 menu items). Do I still need a feed?**
Yes, if you want to publish them as a structured menu. Put them in a tiny `feeds[]` entry pointing to a 5-item JSON-LD file. Or, if you only need to show them on your landing page and don't care about machine consumption, put them in `catalog.signature[]` (≤ 6).

**Q: Will v1 consumers break when I upgrade?**
Yes — that's intentional. v1 readers should see the new `schemaVersion` and stop processing rather than silently misinterpret v2 data. Update your consumers to v2 first, then update your file.

**Q: What about `restaurantDetails`?**
Unchanged. It holds capacity, hours, amenities — that's identity-style metadata, not catalog data.

**Q: Where do I put `featured`?**
At the root, same as v1 — but capped at 12 items per category. Featured items are landing-page picks, not a full catalog.

---

## Rollback

If you need to roll back to v1 temporarily:

1. Restore the previous version of your ofero.json from git history
2. Revert `metadata.schemaVersion` to `"ofero-metadata-1.0"`
3. Pin consumers to the last v1-compatible release of any tooling (e.g. the WordPress shortcodes plugin)

Note that rolling back means accepting the original problems: 2 MB files, slow shortcode renders, mixed identity + catalog concerns. The recommended path is forward.
