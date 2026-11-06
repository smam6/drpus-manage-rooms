# Sanitizers Utility

Provides functions for sanitizing phone numbers, OTPs, prices, IPs, tags, card numbers, and IBAN (Shaba) numbers.

---

## Methods

### phone
Normalize and validate Iranian phone numbers. Converts Persian/Arabic characters and normalizes `+98` prefix to `0`.

**Signature:**
```php
public static function phone(string $phone)
```

**Parameters:**
- `$phone` (string): Phone number string.

**Returns:**
- (string) Normalized phone number, or empty string if invalid.

---

### otp
Sanitize OTP code.

**Signature:**
```php
public static function otp($string, $length = 4)
```

**Returns:**
- (int) Sanitized OTP.

---

### price
Sanitize and normalize a price value.

**Signature:**
```php
public static function price($price, $empty_to_zero = true)
```

**Returns:**
- (int|float|string) Sanitized price.

---

### ip
Sanitize IP address.

**Signature:**
```php
public static function ip($string)
```

**Returns:**
- (string) Valid IP or empty string.

---

### tag
Ensure a string is a valid HTML tag from custom tags.

**Signature:**
```php
public static function tag($string): string
```

**Returns:**
- (string) Validated tag.

---

### card_number
Format and validate a credit card number.

**Signature:**
```php
public static function card_number(string $string): string
```

**Returns:**
- (string) 16-digit card number or empty string.

---

### shaba_number
Format and validate an IBAN (Shaba) number.

**Signature:**
```php
public static function shaba_number(string $shaba)
```

**Returns:**
- (string) 24-digit Shaba number or empty string.

**Notes:**
- Use these sanitizers to ensure data integrity and security.
