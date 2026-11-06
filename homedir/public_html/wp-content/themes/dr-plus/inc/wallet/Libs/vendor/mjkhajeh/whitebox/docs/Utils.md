# Utils Class Documentation

The `Utils` class in MJ Whitebox provides a comprehensive set of helper functions for WordPress development. Below is a detailed description of each method, its purpose, parameters, and usage examples.

---

## Methods

### check_default
Checks and applies default values to an array based on provided defaults and skip indexes. Also checks the type of the value based on defaults.
- **Signature:** `check_default($value, $defaults, $skips = [], $fill_empty = false)`
- **Parameters:**
  - `$value` (array): The input array.
  - `$defaults` (array): Default values.
  - `$skips` (array): Indexes to skip.
  - `$fill_empty` (bool): Fill empty keys with defaults.
- **Returns:** (array) Array with defaults applied.
- **Example:**
  ```php
  $settings = Utils::check_default($settings, [ 'type' => 'primary', 'enabled' => true ]);
  ```

### to_bool
Converts a value to boolean, handling common string representations and WordPress error/null values.
- **Signature:** `to_bool($value)`
- **Parameters:** `$value` (mixed)
- **Returns:** (bool)
- **Example:**
  ```php
  $is_enabled = Utils::to_bool('yes'); // true
  ```

### check_requires
Checks if the given value meets specified requirements.
- **Signature:** `check_requires($value, $requires, $check_empty = true)`
- **Parameters:**
  - `$value` (array|object)
  - `$requires` (array|object)
  - `$check_empty` (bool)
- **Returns:** (bool)
- **Example:**
  ```php
  $is_valid = Utils::check_requires($data, ['name', 'email']);
  ```

### remove_empty_indexes
Removes empty indexes from an array.
- **Signature:** `remove_empty_indexes($array, $all_empty_values = false, $remove_nulls = false)`
- **Parameters:**
  - `$array` (array)
  - `$all_empty_values` (bool)
  - `$remove_nulls` (bool)
- **Returns:** (array)
- **Example:**
  ```php
  $filtered = Utils::remove_empty_indexes($input);
  ```

### unset
Unsets indexes if they exist from an array or object.
- **Signature:** `unset($data, $removes, $skips = [])`
- **Parameters:**
  - `$data` (array|object)
  - `$removes` (array)
  - `$skips` (array)
- **Returns:** (array|object)
- **Example:**
  ```php
  $cleaned = Utils::unset($data, ['password', 'token']);
  ```

### extract
Extracts selected keys from an array or object.
- **Signature:** `extract($data, $keys)`
- **Parameters:**
  - `$data` (array|object)
  - `$keys` (array)
- **Returns:** (array)
- **Example:**
  ```php
  $subset = Utils::extract($user, ['id', 'email']);
  ```

### obj_to_array
Converts an object to an array.
- **Signature:** `obj_to_array($obj, $force = false)`
- **Parameters:**
  - `$obj` (object)
  - `$force` (bool)
- **Returns:** (array)
- **Example:**
  ```php
  $array = Utils::obj_to_array($object);
  ```

### array_to_obj
Converts an array to an object.
- **Signature:** `array_to_obj($array, $force = false)`
- **Parameters:**
  - `$array` (array)
  - `$force` (bool)
- **Returns:** (object)
- **Example:**
  ```php
  $object = Utils::array_to_obj($array);
  ```

### array_flatten
Flattens a multi-dimensional array into a single-dimensional array.
- **Signature:** `array_flatten($items)`
- **Parameters:** `$items` (array)
- **Returns:** (array)
- **Example:**
  ```php
  $flat = Utils::array_flatten($nested);
  ```

### convert_chars
Convert and sanitize string, including Persian/English character conversion.
- **Signature:** `convert_chars($string, $sanitize = 'sanitize_text_field', $sanitize_after = '', $reverse = false)`
- **Parameters:**
  - `$string` (string)
  - `$sanitize` (string|array|bool|callable)
  - `$sanitize_after` (string|array|bool|callable)
  - `$reverse` (bool)
- **Returns:** (string)
- **Example:**
  ```php
  $clean = Utils::convert_chars('۱۲۳۴۵۶'); // "123456"
  ```

### is_json
Checks if a string is valid JSON.
- **Signature:** `is_json($string)`
- **Parameters:** `$string` (string)
- **Returns:** (bool)
- **Example:**
  ```php
  $is_json = Utils::is_json('{"a":1}');
  ```

### ensure_values_in_array
Ensures that values in the source array are allowed according to the specified set of allowed values.
- **Signature:** `ensure_values_in_array($source, $allowed_values, $default = '')`
- **Parameters:**
  - `$source` (mixed)
  - `$allowed_values` (array)
  - `$default` (mixed)
- **Returns:** (mixed)
- **Example:**
  ```php
  $type = Utils::ensure_values_in_array('personal', ['personal', 'legal'], 'personal');
  ```

### convert_to_pascal_case
Converts a string to PascalCase.
- **Signature:** `convert_to_pascal_case($input)`
- **Parameters:** `$input` (string)
- **Returns:** (string)
- **Example:**
  ```php
  $pascal = Utils::convert_to_pascal_case('my_function_name'); // "MyFunctionName"
  ```

### reposition_array_element
Reposition an array element by its key.
- **Signature:** `reposition_array_element(array &$array, $key, int $order)`
- **Parameters:**
  - `$array` (array)
  - `$key` (string|int)
  - `$order` (int)
- **Returns:** (void)
- **Example:**
  ```php
  Utils::reposition_array_element($arr, 'foo', 0);
  ```

### show_errors
Displays errors by adjusting the PHP error reporting settings.
- **Signature:** `show_errors(bool $check_debug_is_active = true, array $users = [])`
- **Parameters:**
  - `$check_debug_is_active` (bool)
  - `$users` (array)
- **Returns:** (void)
- **Example:**
  ```php
  Utils::show_errors();
  ```

### add_zero
Convert the number to a string and add zero if it's lower than 10 or -10.
- **Signature:** `add_zero($number)`
- **Parameters:** `$number` (string|int|float)
- **Returns:** (string)
- **Example:**
  ```php
  $num = Utils::add_zero(5); // "05"
  ```

### time_leading_zero
Adds leading zeros to a time string.
- **Signature:** `time_leading_zero($length)`
- **Parameters:** `$length` (string)
- **Returns:** (string)
- **Example:**
  ```php
  $time = Utils::time_leading_zero('1:2:3'); // "01:02:03"
  ```

### prepare_html_classes
Convert an array of HTML classes to a string.
- **Signature:** `prepare_html_classes(array $classes, $add_class_arg = false)`
- **Parameters:**
  - `$classes` (array)
  - `$add_class_arg` (bool)
- **Returns:** (string)
- **Example:**
  ```php
  $class_str = Utils::prepare_html_classes(['btn', 'btn-primary']);
  ```

### get_html_attributes
Generates a string of HTML attributes from an associative array.
- **Signature:** `get_html_attributes(array $attributes, array $skips = [])`
- **Parameters:**
  - `$attributes` (array)
  - `$skips` (array)
- **Returns:** (string)
- **Example:**
  ```php
  $attrs = Utils::get_html_attributes(['class' => 'btn', 'disabled' => true]);
  ```

### minify_hex
Minifies a hexadecimal color code.
- **Signature:** `minify_hex($string, $add_hashtag = true)`
- **Parameters:**
  - `$string` (string)
  - `$add_hashtag` (bool)
- **Returns:** (string)
- **Example:**
  ```php
  $hex = Utils::minify_hex('#FFAA00'); // "#ffaa00"
  ```

### hide
Hide the element if not value and current is the same.
- **Signature:** `hide($value, $current = true, $display = true)`
- **Parameters:**
  - `$value` (mixed)
  - `$current` (mixed)
  - `$display` (bool)
- **Returns:** (string)
- **Example:**
  ```php
  $style = Utils::hide('yes', 'no');
  ```

### absint_pro
Converts a given value to an absolute integer and ensures it falls within a specified range.
- **Signature:** `absint_pro($string, $min = null, $max = null)`
- **Parameters:**
  - `$string` (mixed)
  - `$min` (int|null)
  - `$max` (int|null)
- **Returns:** (int)
- **Example:**
  ```php
  $num = Utils::absint_pro(-5, 0, 10); // 5
  ```

### check_var_type
Converts a given value to a specified data type.
- **Signature:** `check_var_type($value, string $type)`
- **Parameters:**
  - `$value` (mixed)
  - `$type` (string)
- **Returns:** (mixed)
- **Example:**
  ```php
  $val = Utils::check_var_type('true', 'bool'); // true
  ```

### check_array_types
Converts the values of an associative array to specified data types.
- **Signature:** `check_array_types(array $array, array $types)`
- **Parameters:**
  - `$array` (array)
  - `$types` (array)
- **Returns:** (array)
- **Example:**
  ```php
  $arr = Utils::check_array_types(['a' => '1'], ['a' => 'int']);
  ```

### number_decimal_format
Formats a number to a decimal format with the appropriate number of decimal places.
- **Signature:** `number_decimal_format($value)`
- **Parameters:** `$value` (float)
- **Returns:** (string)
- **Example:**
  ```php
  $formatted = Utils::number_decimal_format(12.345);
  ```

### human_time
Convert seconds integer to human readable time.
- **Signature:** `human_time($time)`
- **Parameters:** `$time` (int)
- **Returns:** (string)
- **Example:**
  ```php
  $str = Utils::human_time(3661); // "1 hour 1 minute 1 second"
  ```

### second_to_string
Converts seconds into a formatted string containing hours, minutes, and seconds.
- **Signature:** `second_to_string(int $seconds)`
- **Parameters:** `$seconds` (int)
- **Returns:** (string)
- **Example:**
  ```php
  $str = Utils::second_to_string(3661); // "01:01:01"
  ```

### db_placeholder
Create placeholder for use in DB query.
- **Signature:** `db_placeholder($object, $type)`
- **Parameters:**
  - `$object` (array|object)
  - `$type` (string)
- **Returns:** (string)
- **Example:**
  ```php
  $ph = Utils::db_placeholder([1,2,3], '%d'); // "%d, %d, %d"
  ```

### is_iran_timezone
Check if timezone set to Asia/Tehran.
- **Signature:** `is_iran_timezone()`
- **Returns:** (bool)
- **Example:**
  ```php
  $is_tehran = Utils::is_iran_timezone();
  ```

### remove_prefix_from_array_keys
Remove a prefix from the keys of an array.
- **Signature:** `remove_prefix_from_array_keys(array $array, string $prefix)`
- **Parameters:**
  - `$array` (array)
  - `$prefix` (string)
- **Returns:** (array)
- **Example:**
  ```php
  $arr = Utils::remove_prefix_from_array_keys(['foo_bar' => 1], 'foo_');
  ```

### maybe_define
Conditionally define a constant if it hasn't been defined yet.
- **Signature:** `maybe_define($name, $value)`
- **Parameters:**
  - `$name` (string)
  - `$value` (mixed)
- **Returns:** (void)
- **Example:**
  ```php
  Utils::maybe_define('MY_CONST', 123);
  ```

### calc_discount_percentage
Calculate the discount percentage between regular and sale prices.
- **Signature:** `calc_discount_percentage($regular_price, $sale_price)`
- **Parameters:**
  - `$regular_price` (float)
  - `$sale_price` (float)
- **Returns:** (int)
- **Example:**
  ```php
  $percent = Utils::calc_discount_percentage(100, 80); // 20
  ```

### query_string_form_fields
Outputs hidden form inputs for each query string variable.
- **Signature:** `query_string_form_fields($values = null, $exclude = array(), $current_key = '', $return = false)`
- **Parameters:**
  - `$values` (string|array)
  - `$exclude` (array)
  - `$current_key` (string)
  - `$return` (bool)
- **Returns:** (string)
- **Example:**
  ```php
  $fields = Utils::query_string_form_fields($_GET);
  ```

### get_nested_value
Retrieves a nested value from a multi-dimensional array using a dot-notated string path.
- **Signature:** `get_nested_value(array $array, string $path, string $delimiter = '.')`
- **Parameters:**
  - `$array` (array)
  - `$path` (string)
  - `$delimiter` (string)
- **Returns:** (mixed|null)
- **Example:**
  ```php
  $val = Utils::get_nested_value(['a' => ['b' => 1]], 'a.b');
  ```

### is_redux_active
Check whether Redux framework is active.
- **Signature:** `is_redux_active()`
- **Returns:** (bool)
- **Example:**
  ```php
  $active = Utils::is_redux_active();
  ```

### is_wc_active
Check whether WooCommerce is active.
- **Signature:** `is_wc_active()`
- **Returns:** (bool)
- **Example:**
  ```php
  $active = Utils::is_wc_active();
  ```

### is_elementor_active
Check whether Elementor is active and installed.
- **Signature:** `is_elementor_active()`
- **Returns:** (bool)
- **Example:**
  ```php
  $active = Utils::is_elementor_active();
  ```

### is_elementor_pro_active
Check whether Elementor Pro is active and installed.
- **Signature:** `is_elementor_pro_active()`
- **Returns:** (bool)
- **Example:**
  ```php
  $active = Utils::is_elementor_pro_active();
  ```

### custom_tags
Retrieve a list of custom HTML tag options.
- **Signature:** `custom_tags()`
- **Returns:** (array)
- **Example:**
  ```php
  $tags = Utils::custom_tags();
  ```

### get_module_name
Get the module name.
- **Signature:** `get_module_name($index, $module)`
- **Parameters:**
  - `$index` (string|int)
  - `$module` (array|string)
- **Returns:** (string)
- **Example:**
  ```php
  $name = Utils::get_module_name('foo', ['bar' => 'baz']);
  ```

### should_include_module
Check if a module should be included based on its requirements (e.g., plugin dependencies).
- **Signature:** `should_include_module($index, $data = [])`
- **Parameters:**
  - `$index` (string): Module identifier (e.g., 'woocommerce', 'elementor').
  - `$data` (array): Optional requirements array. Defaults to `[]`.
- **Returns:** (bool) Whether the module should be included.
- **Example:**
  ```php
  $should = Utils::should_include_module('woocommerce');
  ```

### sidebars_list
Get list of all registered sidebars.
- **Signature:** `sidebars_list()`
- **Returns:** (array)
- **Example:**
  ```php
  $sidebars = Utils::sidebars_list();
  ```

### get_nav_menu_items_by_location
Get nav menu items by location.
- **Signature:** `get_nav_menu_items_by_location($location, $args = [])`
- **Parameters:**
  - `$location` (string)
  - `$args` (array)
- **Returns:** (array)
- **Example:**
  ```php
  $items = Utils::get_nav_menu_items_by_location('primary');
  ```

### get_archive_post_type
Get the post type for the current archive or queried object.
- **Signature:** `get_archive_post_type()`
- **Returns:** (string)
- **Example:**
  ```php
  $type = Utils::get_archive_post_type();
  ```

### get_icon
Generate an HTML element for an icon.
- **Signature:** `get_icon($icon, $icon_element_class = '')`
- **Parameters:**
  - `$icon` (string|array)
  - `$icon_element_class` (string)
- **Returns:** (string)
- **Example:**
  ```php
  $html = Utils::get_icon(['url' => 'icon.png']);
  ```

### apply_general_variables
Replace general and custom variables in a text string.
- **Signature:** `apply_general_variables(string $text, array $custom_variables = [])`
- **Parameters:**
  - `$text` (string)
  - `$custom_variables` (array)
- **Returns:** (string)
- **Example:**
  ```php
  $str = Utils::apply_general_variables('Welcome to {name}', ['name' => 'Site']);
  ```

### count_decimals
Counts the number of decimal digits in a given number.
- **Signature:** `count_decimals($number)`
- **Parameters:** `$number` (float|int|string)
- **Returns:** (int)
- **Example:**
  ```php
  $dec = Utils::count_decimals(12.345); // 3
  ```

### parse_text_editor
Parses and processes content from a WordPress text editor.
- **Signature:** `parse_text_editor($content)`
- **Parameters:** `$content` (string)
- **Returns:** (string)
- **Example:**
  ```php
  $html = Utils::parse_text_editor($raw_content);
  ```

### get_icon_packs
Gets icon packs from a JSON file and applies a filter hook. Uses static caching per project slug.
- **Signature:** `get_icon_packs()`
- **Returns:** (array) Icon packs with SVG URLs resolved.
- **Example:**
  ```php
  $packs = Utils::get_icon_packs();
  ```

### enqueue_script
Enqueues a script, automatically resolving `.js` vs `.min.js` based on `DEV_MODE`. Falls back to whichever variant exists.
- **Signature:** `enqueue_script(string $handle, string $src, array $deps = ['jquery'], $ver = '', bool $in_footer = true)`
- **Parameters:**
  - `$handle` (string): Unique script name.
  - `$src` (string): Script URL or path (extension optional).
  - `$deps` (array): Script dependencies.
  - `$ver` (string): Script version. Defaults to `static::$version`.
  - `$in_footer` (bool): Load before `</body>`. Default `true`.
- **Returns:** (void)
- **Example:**
  ```php
  Utils::enqueue_script('my-plugin', 'assets/js/main.js');
  ```

### src_to_path
Converts a script `$src` URL/path into its physical filesystem path. Handles both full URLs and relative paths.
- **Signature:** `src_to_path(string $src): string`
- **Parameters:**
  - `$src` (string): Full URL or relative path (extension optional).
- **Returns:** (string) Absolute filesystem path.
- **Note:** This is a `protected` method, used internally by `enqueue_script()`.

---

For more details and advanced usage, refer to the source code in `Utils.php`.
