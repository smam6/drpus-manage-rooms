# Formatters Utility

Provides functions for formatting prices, phone numbers, card numbers, and IBAN (Shaba) numbers.

---

## Methods

### price
Get formatted price string, optionally using WooCommerce format.

**Signature:**
```php
public static function price($price, $wc = false, $suffix = '', $decimals = null)
```

**Parameters:**
- `price` (string|number): Price value.
- `wc` (bool): Use WooCommerce format.
- `suffix` (string): Suffix for price.
- `decimals` (int|null): Decimal places.

**Returns:**
- (string) Formatted price.

---

### phone
Format string as phone number by Iran format.

**Signature:**
```php
public static function phone(string $string, bool $reverse = false): string
```

**Returns:**
- (string) Formatted phone number.

---

### card_number
Format a credit card number in groups of 4 digits.

**Signature:**
```php
public static function card_number(string $string, bool $reverse = false): string
```

**Returns:**
- (string) Formatted card number.

---

### shaba_number
Format an IBAN (Shaba) number into standard groups.

**Signature:**
```php
public static function shaba_number(string $string, bool $reverse = false): string
```

**Returns:**
- (string) Formatted Shaba number.

**Notes:**
- Use these formatters to standardize display of financial and contact data.
