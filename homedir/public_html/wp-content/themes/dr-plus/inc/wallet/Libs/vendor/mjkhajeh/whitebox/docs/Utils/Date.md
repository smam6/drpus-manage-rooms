# Date Utility

Provides functions for working with Jalali (Persian) and Gregorian dates, conversion, and formatting.

---

## Methods

### tr_num
Converts numbers between Persian and English formats.

**Signature:**
```php
public static function tr_num($str, $mod = 'en', $mf = '\u066b')
```

**Parameters:**
- `str` (string): Input string.
- `mod` (string): 'fa' for Persian, 'en' for English.
- `mf` (string): Decimal separator.

**Returns:**
- (string) Converted string.

---

### jdate_words
Returns Persian words for date parts (month, day, etc.).

**Signature:**
```php
public static function jdate_words($array, $mod = '')
```

**Parameters:**
- `array` (array): Date parts.
- `mod` (string): Separator.

**Returns:**
- (array|string) Persian words or joined string.

---

### jdate
Formats a Jalali date string.

**Signature:**
```php
public static function jdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
```

**Parameters:**
- `format` (string): Date format.
- `timestamp` (string): Timestamp.
- `time_zone` (string): Timezone.
- `tr_num` (string): Number format.

**Returns:**
- (string) Formatted Jalali date.

---

### gregorian_to_jalali / jalali_to_gregorian
Convert between Gregorian and Jalali dates.

**Signature:**
```php
public static function gregorian_to_jalali($gy, $gm, $gd, $mod='')
public static function jalali_to_gregorian($jy, $jm, $jd, $mod='')
```

**Parameters:**
- Year, month, day.
- `mod` (string): Separator.

**Returns:**
- (array|string) Converted date.

---

### j2g / g2j
Convert Jalali date to Gregorian and vice versa (Y-m-d H:i:s or Y-m-d format).

**Signature:**
```php
public static function j2g(string $date): string
public static function g2j(string $date): string
```

**Parameters:**
- `date` (string): Date string.

**Returns:**
- (string) Converted date.

---

### maybe_j2g / maybe_g2j
Check and convert Jalali/Gregorian if needed.

**Signature:**
```php
public static function maybe_j2g(string $date): string
public static function maybe_g2j(string $date): string
```

**Parameters:**
- `date` (string): Date string.

**Returns:**
- (string) Converted date if needed.

---

### get_time_period
Returns Persian label for a time period (morning, noon, etc.)

**Signature:**
```php
public static function get_time_period(int $hour)
```

**Parameters:**
- `hour` (int): Hour of day.

**Returns:**
- (string) Time period label.

---

### first_day_of_jalali_month / last_day_of_jalali_month
Get Gregorian date for first/last day of current Jalali month.

**Signature:**
```php
public static function first_day_of_jalali_month()
public static function last_day_of_jalali_month()
```

**Returns:**
- (string) Gregorian date in 'Y-m-d' format.

**Notes:**
- Useful for Persian calendar integrations.
- Handles leap years and month rules.

---

### timezone_offset
Get the current timezone offset in seconds for the WordPress site.

**Signature:**
```php
public static function timezone_offset()
```

**Returns:**
- (float) Timezone offset in seconds.

**Details:**
- Uses the WordPress timezone setting (`timezone_string` option) if set, otherwise falls back to the GMT offset.
- Returns the offset in seconds, suitable for date calculations and conversions.
- Example usage:
  ```php
  $offset = Date::timezone_offset();
  ```

---

### get_day_names
Get an array of day names (Sunday through Saturday). Optionally reordered for the Iranian week (Saturday start).

**Signature:**
```php
public static function get_day_names($iran = false)
```

**Parameters:**
- `$iran` (bool): If `true`, returns days starting from Saturday (Iranian week order).

**Returns:**
- (array) Localized day name strings.

**Example:**
```php
$days = Date::get_day_names(); // ['Sunday', 'Monday', ..., 'Saturday']
$ir_days = Date::get_day_names(true); // ['Saturday', 'Sunday', ..., 'Friday']
```

---

### is_iran_timezone
Check if the WordPress site timezone is set to Asia/Tehran.

**Signature:**
```php
public static function is_iran_timezone()
```

**Returns:**
- (bool) `true` if timezone is `Asia/Tehran` or `+03:30`.

---

### time_to_seconds
Convert a time string into total seconds. Supports `SS`, `MM:SS`, and `HH:MM:SS` formats.

**Signature:**
```php
public static function time_to_seconds(string $time): int
```

**Parameters:**
- `$time` (string): Time string in a supported format.

**Returns:**
- (int) Total number of seconds.

**Example:**
```php
Date::time_to_seconds('17');      // 17
Date::time_to_seconds('05:29');   // 329
Date::time_to_seconds('1:05:29'); // 3929
```
