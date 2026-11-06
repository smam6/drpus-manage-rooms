# WC Utility

Provides helper functions for WooCommerce integration, account endpoints, cart, coupons, and currency.

---

## Methods

### get_account_endpoint
Retrieve the current WooCommerce account endpoint slug.

**Signature:**
```php
public static function get_account_endpoint($items = [])
```

**Returns:**
- (string) Endpoint slug.

---

### get_cart_count
Get the number of items in the WooCommerce cart.

**Signature:**
```php
public static function get_cart_count(): int
```

**Returns:**
- (int) Cart item count.

---

### get_active_coupons_for_user
Get all active WooCommerce coupons available for the current user.

**Signature:**
```php
public static function get_active_coupons_for_user()
```

**Returns:**
- (array[WC_Coupon]) Array of active coupons.

---

### ir_currencies
Returns an array of Iranian currencies and their conversion rates to Rial.

**Signature:**
```php
public static function ir_currencies()
```

**Returns:**
- (array) Associative array: `IRR => 1`, `IRT => 0.1`, `IRHR => 0.001`, `IRHT => 0.0001`.

---

### ir_currencies_label
Returns localized labels for Iranian currencies.

**Signature:**
```php
public static function ir_currencies_label()
```

**Returns:**
- (array) Associative array of currency codes to translated labels.

---

### get_gallery_ids
Get all image attachment IDs for a WooCommerce product, including the main image and variation images for variable products.

**Signature:**
```php
public static function get_gallery_ids($product)
```

**Parameters:**
- `$product` (WC_Product): WooCommerce product object.

**Returns:**
- (array) Array of unique attachment IDs.

---

### get_product_type_by_id
Get the WooCommerce product type for a given product ID. Uses a static cache per request.

**Signature:**
```php
public static function get_product_type_by_id($product_id)
```

**Parameters:**
- `$product_id` (int): Product ID.

**Returns:**
- (string) Product type (e.g., `'simple'`, `'variable'`, `'grouped'`).
