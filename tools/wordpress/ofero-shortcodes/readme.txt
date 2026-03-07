=== Ofero Shortcodes ===
Contributors: oferome
Tags: ofero, shortcodes, business info, structured data, json
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.3.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display data from your ofero.json file using simple shortcodes. Compatible with Elementor, WPBakery, Gutenberg, and any theme.

== Description ==

Ofero Shortcodes allows you to display information from your ofero.json file anywhere on your WordPress site using simple shortcodes.

**ofero.json** is a universal standard for representing business and organization information in a structured, machine-readable format. This plugin makes it easy to display that data on your website.

= Features =

* **Simple shortcodes** - Use `[ofero field="path.to.field"]` to display any field
* **Pre-built shortcodes** - Ready-made shortcodes for common use cases
* **Native Elementor widgets** - Drag & drop widgets with visual controls
* **Caching** - Built-in caching for optimal performance
* **Flexible** - Works with local files or external URLs
* **Compatible** - Works with Elementor, WPBakery, Gutenberg, Classic Editor, and any theme

= Available Shortcodes =

**Basic Field Display:**
`[ofero field="organization.legalName"]`
`[ofero field="organization.contactEmail" link="true"]`
`[ofero field="locations.0.address.city"]`

**Organization Card:**
`[ofero_organization show="name,email,phone,website"]`

**Location Information:**
`[ofero_location index="0" show="name,address,phone"]`

**Social Media Links:**
`[ofero_social icons="true"]`

**Banking Information:**
`[ofero_banking index="0" show="bank,iban,bic"]`

**Logo Display:**
`[ofero_logo variant="light" width="200px"]`

**Business Hours:**
`[ofero_hours location="0" format="table"]`

**Location Map:**
`[ofero_map location="0" width="100%" height="400px"]`

**Team Members:**
`[ofero_team type="leadership" columns="3"]`

**Certificates:**
`[ofero_certificates show="name,issuer,date,link" columns="2"]`

**Promo Codes:**
`[ofero_promo show="code,description,discount" active_only="true"]`

**Contact Form:**
`[ofero_contact_form fields="name,email,phone,subject,message"]`

= Field Paths =

Use dot notation to access nested fields. Array indices are supported:

* `organization.legalName` - Organization legal name
* `organization.brandName` - Brand name
* `organization.contactEmail` - Contact email
* `organization.contactPhone` - Contact phone
* `locations.0.name` - First location name
* `locations.0.address.city` - First location city
* `banking.0.iban` - First bank IBAN
* `communications.social.0.url` - First social media URL

== Installation ==

1. Upload the `ofero-shortcodes` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place your `ofero.json` file in `/.well-known/ofero.json` (or configure a different path)
4. Go to Settings > Ofero Shortcodes to configure the plugin
5. Use shortcodes in your posts, pages, or widgets

== Frequently Asked Questions ==

= Where should I put my ofero.json file? =

By default, the plugin looks for `/.well-known/ofero.json` in your WordPress root directory. You can change this path in the plugin settings, or use an external URL.

= How do I access array items? =

Use numeric indices with dot notation. For example:
* `locations.0.name` - First location
* `locations.1.name` - Second location
* `banking.0.iban` - First bank account IBAN

= How do I clear the cache? =

Go to Settings > Ofero Shortcodes and click the "Clear Cache" button. The cache automatically refreshes every hour.

= Does this work with Elementor? =

Yes! The plugin includes native Elementor widgets with drag & drop interface and visual controls:
* **Ofero Field** - Display any field with dropdown selector
* **Ofero Organization** - Organization card with field selection
* **Ofero Location** - Location information display
* **Ofero Social Media** - Social links with icon options
* **Ofero Banking** - Banking details display

All widgets are in the "Ofero" category in Elementor's widget panel. You can also use shortcodes in text/shortcode widgets.

= Can I use an external ofero.json file? =

Yes. Enter the full URL in the "External URL" setting. The plugin will fetch and cache the data.

== Screenshots ==

1. Plugin settings page
2. Example shortcode usage
3. Organization card output

== Changelog ==

= 1.3.0 =
* NEW: [ofero_team] shortcode for displaying team members (leadership, advisors, investors)
* NEW: [ofero_certificates] shortcode for certifications display
* NEW: [ofero_promo] shortcode for promotional codes with expiry filtering
* NEW: [ofero_contact_form] shortcode with auto-populated recipient from ofero.json
* NEW: Contact form email handler with security validation
* IMPROVED: Comprehensive CSS styling for all new shortcodes
* IMPROVED: Grid-based layouts with customizable columns
* Total shortcodes: 12 (complete coverage of ofero.json sections)

= 1.2.0 =
* NEW: [ofero_logo] shortcode for displaying brand logos
* NEW: [ofero_hours] shortcode for business hours (table or list format)
* NEW: [ofero_map] shortcode for embedding location maps
* IMPROVED: Better CSS styling for all shortcodes
* IMPROVED: More complete shortcode coverage

= 1.1.0 =
* NEW: Native Elementor widgets integration
* NEW: 5 drag & drop Elementor widgets (Field, Organization, Location, Social, Banking)
* NEW: Visual controls and field selectors in Elementor
* NEW: Dedicated "Ofero" category in Elementor widget panel
* IMPROVED: Better admin page with Elementor status
* FIXED: Clear cache button URL
* Enhanced compatibility with page builders

= 1.0.0 =
* Initial release
* Basic [ofero] shortcode for any field
* [ofero_organization] shortcode for organization display
* [ofero_location] shortcode for location display
* [ofero_social] shortcode for social media links
* [ofero_banking] shortcode for banking information
* Built-in caching system
* Admin settings page

== Upgrade Notice ==

= 1.1.0 =
Major update with native Elementor integration! Now includes 5 drag & drop widgets with visual controls.

= 1.0.0 =
Initial release of Ofero Shortcodes plugin.
