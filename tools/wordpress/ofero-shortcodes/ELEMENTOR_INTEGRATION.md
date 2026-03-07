# Elementor Integration for Ofero Shortcodes

Version 1.1.0 introduces native Elementor integration with 5 custom widgets.

## Features

- **Native Elementor widgets** - Drag & drop interface
- **Visual controls** - No need to remember shortcode syntax
- **Field selectors** - Dropdown menus for common fields
- **Dedicated category** - All widgets in "Ofero" category
- **Style controls** - Typography, colors, spacing
- **Live preview** - See changes in Elementor editor

## Available Widgets

### 1. Ofero Field Widget
Display any field from ofero.json with dropdown selector or custom path.

**Features:**
- Dropdown selector for common fields (Legal Name, Brand Name, Email, Phone, etc.)
- Custom field path input for advanced usage
- Auto-link option for emails/URLs/phones
- Default value for empty fields
- Typography and color controls

**Usage in Elementor:**
1. Drag "Ofero Field" widget to your page
2. Select a common field OR enter custom path
3. Optionally enable auto-linking
4. Style with typography/color controls

### 2. Ofero Organization Widget
Display organization information as a formatted card.

**Features:**
- Select which fields to display (name, legal name, description, email, phone, website)
- Multiple field selection
- Typography and color controls
- Custom CSS class option

**Usage in Elementor:**
1. Drag "Ofero Organization" widget to your page
2. Select fields to display (multi-select)
3. Customize appearance with style controls

### 3. Ofero Location Widget
Display location information with full address and contact details.

**Features:**
- Select which location to display (by index)
- Choose fields (name, type, address, phone, email, hours)
- Typography and color controls
- Custom CSS class option

**Usage in Elementor:**
1. Drag "Ofero Location" widget to your page
2. Choose location index (0 = first location, 1 = second, etc.)
3. Select fields to display
4. Style as needed

### 4. Ofero Social Media Widget
Display social media links with optional icons.

**Features:**
- Toggle icon display on/off
- Filter specific platforms (comma-separated)
- Adjustable icon size and spacing
- Link color and hover color controls

**Usage in Elementor:**
1. Drag "Ofero Social Media" widget to your page
2. Toggle icon display
3. Optionally filter platforms (e.g., "facebook,instagram,twitter")
4. Adjust spacing and colors

### 5. Ofero Banking Widget
Display banking information for payments.

**Features:**
- Select which bank account to display (by index)
- Choose fields (account name, bank name, IBAN, BIC, currency)
- Label and text color controls
- Typography controls

**Usage in Elementor:**
1. Drag "Ofero Banking" widget to your page
2. Choose account index (0 = first account)
3. Select fields to display
4. Style labels and text

## Installation

The Elementor integration is automatically enabled when:
1. Ofero Shortcodes plugin is installed and activated
2. Elementor is installed and activated

No additional configuration needed!

## Finding Widgets

All Ofero widgets are in the **"Ofero"** category in Elementor's widget panel:
1. Open Elementor editor
2. Click "+" to add widget
3. Look for "Ofero" category
4. Drag desired widget to your page

## Widget Icons

- **Ofero Field** - Database icon (eicon-database)
- **Ofero Organization** - Info circle icon (eicon-info-circle)
- **Ofero Location** - Map pin icon (eicon-map-pin)
- **Ofero Social Media** - Social icons (eicon-social-icons)
- **Ofero Banking** - Price table icon (eicon-price-table)

## Technical Details

### Widget Registration
Widgets are registered via the `elementor/widgets/widgets_registered` action hook.

### Category Registration
A custom "Ofero" category is created via `elementor/elements/categories_registered` hook.

### Widget Files
All widget classes are in `includes/elementor/` directory:
- `class-elementor-ofero-field-widget.php`
- `class-elementor-ofero-organization-widget.php`
- `class-elementor-ofero-location-widget.php`
- `class-elementor-ofero-social-widget.php`
- `class-elementor-ofero-banking-widget.php`

### Compatibility
Tested with:
- Elementor 3.x
- WordPress 5.0+
- PHP 7.4+

## Fallback
If Elementor is not active, the plugin still works perfectly with shortcodes. The Elementor integration is purely additive.

## Support

For issues or questions:
- Check Settings > Ofero Shortcodes for status
- Green checkmark means Elementor integration is active
- Orange warning means Elementor is not detected

## Developer Notes

### Extending Widgets
You can create custom widgets by:
1. Extending `\Elementor\Widget_Base`
2. Implementing required methods
3. Registering via `elementor/widgets/widgets_registered` hook

### Widget Structure
```php
class Custom_Ofero_Widget extends \Elementor\Widget_Base {
    public function get_name() { /* widget ID */ }
    public function get_title() { /* display title */ }
    public function get_icon() { /* icon class */ }
    public function get_categories() { return ['ofero']; }
    protected function register_controls() { /* widget controls */ }
    protected function render() { /* frontend output */ }
}
```

### Shortcode Integration
All widgets use `do_shortcode()` to render content, ensuring consistency between shortcodes and widgets.
