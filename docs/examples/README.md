# Examples

Ready-to-use `ofero.json` files for different business types. Pick the one closest to your business, copy it, and edit with your own data.

---

## By Business Type

| Business type | Example file | Key sections used |
|---|---|---|
| Minimal (any business) | [`minimal.json`](minimal.json) | organization only |
| Company / SaaS | [`company-full.json`](company-full.json) | organization, locations, banking, branding, communications, APIs |
| Restaurant / Pizzeria | [`restaurant-example.json`](restaurant-example.json) | catalog.menu, catalog.dailyMenu, restaurantDetails |
| Hotel | [`hotel-example.json`](hotel-example.json) | rooms, locations, restaurantDetails |
| E-commerce store | [`ecommerce-store.json`](ecommerce-store.json) | catalog, featured, promotions |
| Auto service / Mechanic | [`auto-service-example.json`](auto-service-example.json) | catalog.services, locations, businessHours |
| Architecture firm | [`architecture-firm-example.json`](architecture-firm-example.json) | catalog.portfolio, team |
| Modeling agency | [`modeling-agency-example.json`](modeling-agency-example.json) | catalog.portfolio, team |
| Karting / Experience | [`karting-example.json`](karting-example.json) | catalog.packages, locations |
| Web3 / DeFi protocol | [`web3-protocol.json`](web3-protocol.json) | wallets, tokenomics, security, verification |
| Multi-platform company | [`company-with-both-platforms.json`](company-with-both-platforms.json) | platformAccounts, communications |

---

## Restaurant — What's Supported

The restaurant example covers everything a food business needs:

### Menu categories with service hours
```json
{
  "id": "breakfast",
  "name": { "default": "Breakfast" },
  "serviceHours": "08:00-12:00",
  "sortOrder": 1,
  "items": []
}
```

### Menu items with ingredients and portion size

Prices are expressed as a number in `price`, with the currency defined once at `catalog.defaultCurrency`. Systems consuming the file should format prices themselves. Use `priceUnit` only when the pricing basis needs clarification (e.g. `"per glass"`, `"per kg"`).

```json
{
  "id": "carbonara",
  "name": { "default": "Spaghetti Carbonara" },
  "portionSize": "320g",
  "price": 18,
  "ingredients": ["spaghetti", "guanciale", "egg yolk", "pecorino romano", "black pepper"],
  "dietary": [],
  "allergens": ["gluten", "eggs", "dairy"],
  "available": true,
  "popular": true
}
```

For items priced per unit other than the default portion (e.g. wine by the glass):

```json
{
  "id": "house-wine",
  "name": { "default": "House Wine" },
  "price": 18,
  "priceUnit": "per glass",
  "portionSize": "150ml"
}
```

### Weekly daily menu

`dailyMenu` represents the current week's specials. It is part of the live file — the restaurant updates it directly, and external portals or AI systems that fetch `ofero.json` regularly will always get the current week. Each item in the daily menu is standalone (not a reference to `catalog.menu`) because portion sizes and prices typically differ from the regular menu.

```json
"dailyMenu": {
  "weekOf": "2026-03-09",
  "note": { "default": "Menu changes every Monday." },
  "schedule": {
    "monday": [
      {
        "name": { "default": "Tomato Cream Soup" },
        "portionSize": "330g",
        "price": 8,
        "course": "soup",
        "ingredients": ["tomatoes", "mascarpone", "basil", "croutons"],
        "allergens": ["dairy", "gluten"]
      },
      {
        "name": { "default": "Spaghetti Carbonara" },
        "portionSize": "320g",
        "price": 14,
        "course": "main",
        "ingredients": ["spaghetti", "guanciale", "egg yolk", "pecorino romano"],
        "allergens": ["gluten", "eggs", "dairy"]
      }
    ]
  }
}
```

`course` values: `starter` `soup` `main` `dessert` `drink` `side` `other`

`dietary` values: `vegetarian` `vegan` `gluten-free` `halal` `kosher` `dairy-free` `nut-free` `organic` `spicy` `contains-alcohol`

`allergens` values: `gluten` `dairy` `eggs` `fish` `shellfish` `tree-nuts` `peanuts` `soy` `sesame` `sulfites` `celery` `mustard` `lupin` `molluscs`

---

## Auto Service — What's Supported

Use `catalog.services` for service offerings with pricing:

```json
"catalog": {
  "defaultCurrency": "USD",
  "services": [
    {
      "id": "oil-change",
      "name": { "default": "Oil Change" },
      "description": { "default": "Full synthetic oil change with filter replacement" },
      "price": 49,
      "duration": "30 min",
      "available": true
    }
  ]
}
```

---

## E-commerce — What's Supported

Use `catalog.productFeeds` for large catalogs, or inline `items` for small ones:

```json
"catalog": {
  "defaultCurrency": "USD",
  "productFeeds": [
    {
      "type": "products",
      "format": "json",
      "url": "https://yourstore.com/feed/products.json",
      "description": "Full product catalog"
    }
  ]
}
```

---

## Web3 / Protocol — What's Supported

Use `wallets`, `tokenomics`, `verification`, and `security` sections. See [`web3-protocol.json`](web3-protocol.json) for a complete example including wallet ownership proofs, token metadata, and DEX information.

---

## Tools

### WordPress
Copy `tools/wordpress/ofero-generator/` into `wp-content/plugins/`, activate, and use the admin UI. Select your business type — only relevant sections appear.

### PHP (custom site)
Use `tools/php-generator/ofero-generator.php` as a starting point for generating the file programmatically.

### Manual
Copy the closest example, edit the values, validate against the schema:

```bash
# Validate using any JSON Schema validator with:
# schema/ofero-json-schema.json
```

Or use the TypeScript validator:

```typescript
import { validateOferoJson } from '../../validators/ofero-json-validator';
const result = await validateOferoJson(data, 'moderate');
```
