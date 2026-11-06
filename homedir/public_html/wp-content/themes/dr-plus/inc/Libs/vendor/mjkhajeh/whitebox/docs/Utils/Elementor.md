# Elementor Utility

Provides helper functions for working with Elementor controls and content.

---

## Methods

### get_wrapper_selector
Ensures a CSS selector string includes the Elementor wrapper placeholder.

**Signature:**
```php
public static function get_wrapper_selector($string)
```

**Parameters:**
- `string` (string): CSS selector.

**Returns:**
- (string) Selector including '{{WRAPPER}}'.

---

### button_types / button_styles
Get available button types and styles for Elementor controls.

**Signature:**
```php
public static function button_types($args = [])
public static function button_styles($args = [])
```

**Parameters:**
- `args` (array): Optional arguments.

**Returns:**
- (array) List of types/styles.

---

### date_types
Get available date types for Elementor controls.

**Signature:**
```php
public static function date_types($args = [])
```

**Returns:**
- (array) List of date types.

---

### orderby
Get orderby options for queries (posts/products).

**Signature:**
```php
public static function orderby($wc = false, $excludes = [], $args = [])
```

**Parameters:**
- `wc` (bool): WooCommerce mode.
- `excludes` (array): Exclude options.
- `args` (array): Additional args.

**Returns:**
- (array) List of orderby options.

---

### get_link_attributes
Generate link HTML attributes from Elementor link settings.

**Signature:**
```php
public static function get_link_attributes($link = [])
```

**Parameters:**
- `link` (array|string): Link settings.

**Returns:**
- (array) HTML attributes.

---

### get_display_attributes
Get display attributes for slider and columns in themes/plugins.

**Signature:**
```php
public static function get_display_attributes(array $settings, $slider_mode = false, $other_slider_attrs = [])
```

**Parameters:**
- `settings` (array): Display settings.
- `slider_mode` (bool): Force slider.
- `other_slider_attrs` (array): Extra slider args.

**Returns:**
- (array) Attributes for rendering.

---

### get_button_args / check_button_defaults
Get and validate button arguments from settings.

**Signature:**
```php
public static function get_button_args(array $settings, string $prefix = 'button_')
public static function check_button_defaults(array $args, string $prefix = 'button_')
```

**Returns:**
- (array) Button arguments.

---

### is_built_with_elementor
Check if a post is built with Elementor.

**Signature:**
```php
public static function is_built_with_elementor($id)
```

**Parameters:**
- `id` (int): Post ID.

**Returns:**
- (bool)

---

### get_content
Get the content of a post built with Elementor.

**Signature:**
```php
public static function get_content($id, $inline_css = false)
```

**Parameters:**
- `id` (int): Post ID.
- `inline_css` (bool): Include inline CSS.

**Returns:**
- (string) Content HTML.

---

### has_link
Check if a link attribute contains a valid URL.

**Signature:**
```php
public static function has_link($link_attr)
```

**Parameters:**
- `link_attr` (array|string)

**Returns:**
- (bool)

---

### button_default_args
Get default button arguments with a given prefix. Handles RTL alignment.

**Signature:**
```php
public static function button_default_args($prefix)
```

**Parameters:**
- `$prefix` (string): Prefix for button argument keys (e.g., `'button_'`).

**Returns:**
- (array) Default button arguments including `transparent`, `type`, `small`, `icon`, `text`, `title`, `link`, `new_tab`, `fullwidth`, `icon_align`, `style`, `align`, `classes`, `id`, `disabled`, `loading`, `atts`.

---

### button_default_args_skips
Get the list of button default argument keys that should be skipped during merging.

**Signature:**
```php
public static function button_default_args_skips($prefix)
```

**Parameters:**
- `$prefix` (string): Prefix for button argument keys.

**Returns:**
- (array) Keys to skip (currently just `{$prefix}icon`).

**Notes:**
- These utilities simplify integration with Elementor widgets and controls.
- Use them to standardize control options and rendering logic.
