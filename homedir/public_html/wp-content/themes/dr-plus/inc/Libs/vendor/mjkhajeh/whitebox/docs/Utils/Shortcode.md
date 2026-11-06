# Shortcode Utility

Provides helper functions for preparing and processing shortcode arguments and attributes.

---

## Methods

### prepare_shortcode_args
Convert array of args to string for placing in shortcode.

**Signature:**
```php
public static function prepare_shortcode_args($args)
```

**Returns:**
- (string) Shortcode argument string.

---

### separated_to_array
Splits specific string values in the given attributes array into arrays based on a defined separator.

**Signature:**
```php
public static function separated_to_array(array $atts, array $separated_keys, string $separator = '&&')
```

**Returns:**
- (array) Modified attributes array.

**Notes:**
- Use these utilities to simplify shortcode argument handling.
