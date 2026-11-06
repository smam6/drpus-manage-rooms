# Validators Utility

Provides functions for validating Iranian ID codes, card numbers, IBAN (Shaba) numbers, phone numbers, and Base64 strings.

---

## Methods

### id_code
Check if Iranian ID code is correct.

**Signature:**
```php
public static function id_code($string)
```

**Returns:**
- (bool)

---

### card_number
Validate a credit card number using the Luhn algorithm.

**Signature:**
```php
public static function card_number($cardNumber)
```

**Returns:**
- (bool)

---

### shaba_number
Validate an IBAN (Shaba) number.

**Signature:**
```php
public static function shaba_number($shaba)
```

**Returns:**
- (bool)

---

### phone
Check if a string is a valid phone number.

**Signature:**
```php
public static function phone($string)
```

**Returns:**
- (bool)

---

### base64
Validate if a string is a valid Base64-encoded value.

**Signature:**
```php
public static function base64($string)
```

**Returns:**
- (bool)

**Notes:**
- Use these validators to ensure data correctness and security.
