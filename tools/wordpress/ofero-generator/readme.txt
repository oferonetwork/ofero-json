=== Ofero Generator ===
Contributors: Ofero Network
Tags: ofero, json, business info, structured data, generator
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 2.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generate and manage your ofero.json file from the WordPress admin, with validation, auto-save and backups.

== Description ==

**v2.0.0 writes ofero.json v2 (schemaVersion `ofero-metadata-2.0`).** Inline catalog data (menus, products, services, packages, portfolios) is no longer allowed — those move to external feeds referenced via `catalog.feeds[]`. WooCommerce products are now served from a built-in REST endpoint and added automatically to the feeds list. See the MIGRATION-v1-to-v2.md guide in the ofero-json-standard repo.

Ofero Generator provides a full-featured WordPress admin interface for creating and managing your ofero.json file - the universal standard for representing business and organization information.

**ofero.json** is a machine-readable metadata file that makes your business information accessible to AI systems (like ChatGPT, Claude, Perplexity), B2B partners, and automated tools.

= Features =

* **Complete Editor** - All ofero.json sections in an intuitive tabbed interface
* **WooCommerce Integration** - Automatically sync products to your catalog
* **Real-time Validation** - Three validation levels (Basic, Moderate, Strict)
* **Auto-save** - Draft auto-saving to prevent data loss
* **Backup System** - Automatic backups before each save
* **Import/Export** - Import from URL or file, export anytime
* **Media Integration** - WordPress media library for brand assets
* **Preview Mode** - See your JSON before publishing
* **Multi-language Support** - Translation system for international businesses
* **Emergency Reset** - Quick recovery from corrupted data

= Sections Included =

* **Basic Info** - Language, domain, canonical URL, version
* **Organization** - Legal name, brand, entity type, registration details
* **Locations** - Multiple physical locations with addresses
* **Banking** - Bank accounts with IBAN/BIC support
* **Wallets** - Blockchain wallet addresses for Web3
* **Branding** - Logos, icons, and brand assets
* **Communications** - Social media and support channels
* **Catalog** - Product catalog with WooCommerce sync
* **Translations** - Multi-language support for international businesses

= Validation =

The plugin validates your ofero.json against the official standard:

* **Basic** - Required fields only
* **Moderate** - Adds format validation (emails, URLs, codes)
* **Strict** - Full validation including IBAN format, country codes

== Installation ==

1. Upload the `ofero-generator` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Ofero.json" in the admin menu
4. Start filling in your organization information
5. Click "Save ofero.json" to publish

The plugin automatically creates the `.well-known` directory and saves your file to `.well-known/ofero.json`.

== Frequently Asked Questions ==

= Where is my ofero.json saved? =

By default, it's saved to `/.well-known/ofero.json` in your WordPress root. You can change this path in Settings.

= Can I import an existing ofero.json? =

Yes! Go to Settings > Import/Export and either paste a URL or upload a JSON file.

= How do backups work? =

Before each save, the plugin creates a backup in `.well-known/backups/`. You can restore or delete backups from the Settings page.

= Is my data validated? =

Yes. The plugin validates your data against the ofero.json standard. You can see validation status on the Editor page tab indicators.

= Can I use the WordPress media library? =

Yes! Brand assets (logos, icons) can be selected from your WordPress media library.

= Does this work with WooCommerce? =

Yes! The plugin includes automatic WooCommerce integration. Go to the Catalog tab and select which products you want to include in your ofero.json. The plugin will automatically convert your WooCommerce products to the ofero.json format with prices, images, categories, and availability.

= What if the plugin stops working or tabs don't respond? =

Go to Settings → Ofero Generator → Emergency Reset section and click "Emergency Reset Plugin Data". This will reset all plugin settings while preserving your ofero.json file and backups. This fixes issues caused by corrupted data or encoding problems.

= Does uninstalling the plugin delete my ofero.json? =

No. When you uninstall the plugin, only the plugin options are removed from the database. Your ofero.json file and backups are preserved so you don't lose any data.

== Screenshots ==

1. Main editor interface with tabbed sections
2. Organization details form
3. Locations repeater field
4. Preview page with validation status
5. Settings and backup management

== Changelog ==

= 2.1.0 =
* NEW: Organization tab has a Business Classification card (industry path, main products/services, target market, operational status), written to `businessClassification`.
* NEW: Service Area card writes `businessClassification.serviceArea` — how you serve customers (at your location, at the customer, remotely), worldwide, countries, and regions/cities. Lets businesses without a public address (remote agencies, online services) say where they serve.
* FIX: `organization.industry` is now written (first item of the industry path). The schema requires it for companies, so saved files for companies were failing schema validation.
* FIX: `organization.brandName`, `organization.description` and `keywords` are always written as TranslatableString objects (`{"default": …}`). Without translations they were written as plain strings, which the v2 schema rejects.
* FIX: the Organization tab reads brand name and description correctly when they are stored as TranslatableString objects (previously it printed "Array" once translations were added).
* FIX: location types. The Type dropdown offered store, warehouse, office, factory and distribution_center, which the schema rejects in `locations[].type`. It is now split into "Role" (`type`: headquarters, branch, international-branch, representative-office) and "Kind of space" (`facility`: office, store, venue, workshop, warehouse, factory, distribution-center). Locations saved with an old type are shown as Branch with the matching kind of space; re-saving converts them.
* FIX: the Branding tab now writes the schema's `branding` object (`logos.vector[]` / `logos.raster[]`, `icons.favicon`, `icons.appIcons[]`, `coverImage`) instead of a flat `brandAssets` list, which was never part of the schema and was ignored by consumers, including the `[ofero_logo]` shortcode. Files with the old `brandAssets` list are shown in the tab and converted on save. `branding.guidelines` and other keys the tab does not edit are kept.
* NEW: each location has a "Public access" field (walk-in, by appointment only, not open to the public), written to `locations[].publicAccess`. Use "not open to the public" for back offices and warehouses, so their hours are not read as visiting hours.
* NEW: validator flags companies without an industry, invalid service-area country codes, invalid location facilities and public access values, non-HTTPS branding URLs and leftover `brandAssets`.

= 2.0.1 =
* FIX: `catalog.signature[].priceFormatted` no longer contains literal HTML entities. WooCommerce `get_price_html()` returns markup with `&nbsp;` between amount and currency; the value was tag-stripped but not entity-decoded, so consumers that escape it rendered "8.66&nbsp;lei". Entities are now decoded, non-breaking spaces normalised and whitespace collapsed.
* FIX: product names and descriptions pass through `wp_check_invalid_utf8()` before JSON encoding, so invalid byte sequences no longer make `wp_json_encode()` drop the whole value. (Text that is already mojibake in the database — latin1 collations, double-encoded imports — still needs fixing at the database level.)
* HARDENING: all `$_POST`/`$_GET`/`$_FILES` reads are unslashed with `wp_unslash()` before sanitizing; admin redirects use `wp_safe_redirect()`; `wp_die()` messages are escaped; template output (`$license_badge`, validation counts, repeater indexes, business-type icons) is escaped.
* CHANGE: `is_writable()` → `wp_is_writable()`, `unlink()` → `wp_delete_file()`, `parse_url()` → `wp_parse_url()`, `date()` → `gmdate()`, uploaded-file reads go through `WP_Filesystem`.
* CHANGE: added `translators:` comments to every translatable string with placeholders; dropped the unused `Domain Path` header; readme tags trimmed to 5 and short description shortened.
* CHANGE: Tested up to bumped to WordPress 7.0.

= 2.0.0 =
* BREAKING: writes ofero.json v2 (`schemaVersion: ofero-metadata-2.0`). v1 files (`ofero-metadata-1.0`) are flagged by the validator with a migration pointer; the next save migrates the file to v2 and drops inline catalog fields.
* BREAKING: inline catalog data (`catalog.menu`, `catalog.dailyMenu`, `catalog.services`, `catalog.packages`, `catalog.portfolio`, `catalog.productFeeds`, `catalog.serviceFeeds`) is no longer written. v2 requires those to live in external feeds referenced via `catalog.feeds[]`.
* NEW: Catalog tab now manages `catalog.feeds[]` entries (type, format, URL, name, language, standard, itemCount), plus inline previews (`catalog.signature[]`, `catalog.highlights[]`, max 6 each).
* NEW: WooCommerce products are now exposed via a built-in REST endpoint at `/wp-json/ofero/v2/products` (no filesystem writes needed). The endpoint URL is automatically added to `catalog.feeds[]` on save when you have products selected, and the first selected products auto-populate `catalog.signature[]` when empty.
* CHANGE: Menu and Services tabs now show an explainer pointing to the Catalog tab — they don't render inline editors anymore.
* CHANGE: Restaurant tab still edits `restaurantDetails` (capacity, hours, amenities — valid in v2), with a note about the menu move.
* NEW: admin notice on plugin pages when the saved ofero.json still declares v1.
* NEW: validator enforces v2 (rejects v1 schemaVersion, rejects inline catalog keys, enforces `signature`/`highlights` ≤ 6, `featured.*` ≤ 12).
* Plugin version bumped to 2.0.0.

= 1.3.0 =
* IMPROVED: Internationalized all registration number and tax ID examples
* IMPROVED: Wider postal code field in locations section for better UX
* IMPROVED: Country field placeholder moved to description for clarity
* NEW: Comprehensive social media format guidelines with platform-specific examples
* NEW: Info boxes explaining Facebook, Instagram, WhatsApp, Telegram URL formats
* IMPROVED: Logo variant descriptions clarified (light = for dark backgrounds, dark = for light backgrounds)
* IMPROVED: Better help text and instructions throughout the interface

= 1.0.0 =
* Initial release
* Complete editor for all ofero.json sections
* Three-level validation system
* Backup and restore functionality
* Import from URL or file
* WordPress media library integration
* Auto-save drafts feature
* Preview with JSON highlighting

== Upgrade Notice ==

= 2.0.1 =
Fixes literal HTML entities (&nbsp;) in catalog.signature[].priceFormatted for WooCommerce sites, and guards product text against invalid UTF-8. Re-save or re-sync to regenerate ofero.json.

= 2.0.0 =
Breaking: writes ofero.json v2. Inline catalog data is dropped on save — full menus/products/services must live in external feeds referenced from catalog.feeds[]. Migrate before saving.

= 1.3.0 =
UX improvements: Better help text, clearer social media format guidelines, and internationalized examples.

= 1.0.0 =
Initial release of Ofero Generator plugin.
