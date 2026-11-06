# Files Utility

Provides helper functions for file management, uploads, and media integration in WordPress.

---

## Methods

### get_upload_dir
Retrieves the upload directory path for storing files.

**Signature:**
```php
public static function get_upload_dir(string $type = 'path')
```

**Parameters:**
- `type` (string): 'path' or 'base'.

**Returns:**
- (string) Full path to upload directory.

---

### get_file_path
Retrieves the full file path for a given filename within the uploads directory.

**Signature:**
```php
public static function get_file_path($filename): string
```

**Parameters:**
- `filename` (string): File name.

**Returns:**
- (string) Full file path.

---

### get_max_upload_size
Get the maximum allowed upload size in bytes.

**Signature:**
```php
public static function get_max_upload_size()
```

**Returns:**
- (int) Max upload size in bytes.

---

### convert_bytes_to_mb / convert_mb_to_bytes
Convert between bytes and megabytes.

**Signature:**
```php
public static function convert_bytes_to_mb(int $bytes, bool $add_suffix = true, int $decimal_places = 0)
public static function convert_mb_to_bytes(int $megabytes): int
```

**Returns:**
- (string|float|int) Converted value.

---

### download
Downloads a file from a URL and saves it to the uploads directory, creating a media attachment.

**Signature:**
```php
public static function download($url, $filename = '')
```

**Parameters:**
- `url` (string): File URL.
- `filename` (string): Optional filename.

**Returns:**
- (int|WP_Error) Attachment ID or error.

**Notes:**
- Handles directory creation and media library integration.
- Useful for programmatically importing files.
