# ElementorControls Class Documentation

The `ElementorControls` class in MJ Whitebox provides advanced helper methods for adding and managing controls in Elementor widgets. It streamlines the process of creating reusable, responsive, and customizable controls for Elementor-based plugins and themes.

---

## Overview
- Designed for WordPress developers building custom Elementor widgets.
- Handles control positioning, exclusions, responsive settings, and advanced style sections.
- Supports general style controls, display settings, pagination, and query controls.

---

## Main Methods

### _add_controls
**Purpose:** Add controls to an Elementor widget, handling exclusions and responsive settings.

**Signature:**
```php
protected static function _add_controls($object, $default_controls, $prefix, $args = [])
```

**Parameters:**
- `object`: Elementor widget instance.
- `default_controls`: Array of default controls.
- `prefix`: Optional prefix for control names.
- `args`: Optional arguments (excludes, controls).

**Usage Example:**
```php
self::_add_controls($widget, $controls, 'prefix_', ['excludes' => ['color']]);
```

---

### general_style_controls
**Purpose:** Add a general style controls section to an Elementor widget (margin, padding, color, background, typography, etc.), with support for normal and hover states and custom modes (svg, icon, text, wrapper, image, input).

**Signature:**
```php
public static function general_style_controls($object, array $args)
```

**Parameters:**
- `object`: Elementor widget instance.
- `args`: Array of arguments (prefix, selectors, section, tabs, excludes, controls, mode).

**Usage Example:**
```php
ElementorControls::general_style_controls($widget, [
    'prefix' => 'card_',
    'base_selector' => '.specialist-card',

    'section' => [
        'name' => 'card_',
        'label' => esc_html__('Specialist card', 'mj-whitebox'),
    ],
	
    'mode' => 'wrap',
]);
```

---

### text_style_controls
**Purpose:** Add text style controls to an Elementor widget, including normal and hover states.

**Signature:**
```php
public static function text_style_controls($object, $selector, $prefix, $label, $hover_selector = '')
```

**Parameters:**
- `object`: Elementor widget instance.
- `selector`: CSS selector.
- `prefix`: Prefix for control names.
- `label`: Section label.
- `hover_selector`: Optional hover selector.

---

### display_settings
**Purpose:** Add display settings controls for responsive sliders and columns (desktop, tablet, mobile).

**Signature:**
```php
public static function display_settings($object, $args = [])
```

**Parameters:**
- `object`: Elementor widget instance.
- `args`: Array of arguments (section, excludes, controls).

---

### pagination_controls
**Purpose:** Add pagination controls for post/product archives.

**Signature:**
```php
public static function pagination_controls($object, $args = [])
```

**Parameters:**
- `object`: Elementor widget instance.
- `args`: Array of arguments (section, excludes, controls).

---

### pagination_style_controls
**Purpose:** Add style controls for pagination elements (numbers, current, prev, next, dots).

**Signature:**
```php
public static function pagination_style_controls($object, bool $wc = false)
```

**Parameters:**
- `object`: Elementor widget instance.
- `wc`: WooCommerce mode.

---

### query_controls
**Purpose:** Add advanced query controls for selecting posts/products, including includes, excludes, date, order, and custom filters.

**Signature:**
```php
public static function query_controls($object, bool $wc = false, array $args = [])
```

**Parameters:**
- `object`: Elementor widget instance.
- `wc`: WooCommerce mode.
- `args`: Array of arguments for customizing query controls.

---

## Style Control Methods

The following methods add individual style controls to widgets:
- `margin`, `padding`, `typography`, `icon_size`, `text_align`, `color`, `placeholder_color`, `background`, `border`, `border_radius`, `box_shadow`, `text_shadow`, `width`, `height`, `display`, `flex_wrap`, `justify_content`, `align_items`, `align_content`, `row_gap`, `column_gap`, `columns`, `css_filters`

**Example:**
```php
ElementorControls::margin($widget, 'margin_id', '.selector');
```

---

## Usage Tips
- Use these methods to quickly add consistent, responsive controls to custom Elementor widgets.
- Supports advanced scenarios like hover states, SVG styling, and WooCommerce integration.
- Combine with MJ Whitebox utilities for maximum flexibility and code reuse.

---

For more details and advanced usage, refer to the source code in `ElementorControls.php`.
