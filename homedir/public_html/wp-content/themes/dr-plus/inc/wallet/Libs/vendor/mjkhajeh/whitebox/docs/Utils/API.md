# API Utility

Provides helper functions for API responses and data formatting.

---

## Methods

### to_lower_before_response
Converts all keys of an array to lowercase recursively.

**Signature:**
```php
public static function to_lower_before_response(array $data): array
```

**Parameters:**
- `data` (array): The array to process.

**Returns:**
- (array) Array with all keys in lowercase.

**Example:**
```php
$response = API::to_lower_before_response(['Name' => 'Ali', 'Data' => ['Age' => 30]]);
// ['name' => 'Ali', 'data' => ['age' => 30]]
```

**Notes:**
- Useful for standardizing API output keys.
- Handles nested arrays automatically.

---

### convert_id_to_attachment_array
Convert a WordPress attachment ID to an array with `id` and `url`.

**Signature:**
```php
public static function convert_id_to_attachment_array($id)
```

**Parameters:**
- `$id` (int): Attachment ID.

**Returns:**
- (array) `['id' => int, 'url' => string]`

---

### convert_id_to_attachment_url
Convert a WordPress attachment ID to its URL.

**Signature:**
```php
public static function convert_id_to_attachment_url($id)
```

**Parameters:**
- `$id` (int): Attachment ID.

**Returns:**
- (string) Attachment URL.

---

### convert_array_ids_to_attachment_array
Convert an array of attachment IDs to an array of `['id', 'url']` arrays.

**Signature:**
```php
public static function convert_array_ids_to_attachment_array($ids)
```

**Parameters:**
- `$ids` (array): Array of attachment IDs.

**Returns:**
- (array) Array of `['id' => int, 'url' => string]` entries.

---

### convert_array_ids_to_attachment_url
Convert an array of attachment IDs to an array of URLs.

**Signature:**
```php
public static function convert_array_ids_to_attachment_url($ids)
```

**Parameters:**
- `$ids` (array): Array of attachment IDs.

**Returns:**
- (array) Array of URL strings.
