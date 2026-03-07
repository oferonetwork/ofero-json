=== Ofero Generator ===
Contributors: Ofero Network
Tags: ofero, json, business info, structured data, generator, editor
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.3.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A complete admin interface for generating and managing ofero.json files with validation, auto-save, and backup functionality.

== Description ==

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

= 1.3.0 =
UX improvements: Better help text, clearer social media format guidelines, and internationalized examples.

= 1.0.0 =
Initial release of Ofero Generator plugin.
