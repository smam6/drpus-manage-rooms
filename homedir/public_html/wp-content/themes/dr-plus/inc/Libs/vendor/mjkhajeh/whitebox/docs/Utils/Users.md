# Users Utility

Provides helper functions for working with WordPress users, user meta, and user creation.

---

## Methods

### get_user_object
Get a user object by ID, object, or array.

**Signature:**
```php
public static function get_user_object($user = null)
```

**Returns:**
- (WP_User|null) User object or null.

---

### get_user_id
Get the ID of a user by various means.

**Signature:**
```php
public static function get_user_id($user = 0)
```

**Returns:**
- (int) User ID or 0.

---

### find_user_by_mobile
Find a WordPress user by mobile number.

**Signature:**
```php
public static function find_user_by_mobile(string $mobile, $return = 'ID')
```

**Parameters:**
- `$mobile` (string): Mobile number.
- `$return` (string): Return type — `'ID'` for user ID, `'OBJECT'` for full user object.

**Returns:**
- (int|WP_User|string) User ID, user object, or empty string if not found.

---

### get_user_mobile
Retrieve a user's mobile number.

**Signature:**
```php
public static function get_user_mobile($user_id)
```

**Returns:**
- (string) Mobile number or empty string.

---

### create_user
Create a new WordPress user with optional mobile, email, password, and meta.

**Signature:**
```php
public static function create_user($username, $password = '', $email = '', $mobile = '', $meta = [])
```

**Returns:**
- (int|WP_Error) New user ID or error.

---

### get_avatar_id / save_avatar_id
Get or save a user's avatar ID.

**Signature:**
```php
public static function get_avatar_id($user_id = 0)
public static function save_avatar_id($avatar_id, $user_id = 0)
```

**Returns:**
- (int) Avatar ID or void.

**Notes:**
- Use these utilities to manage user data and meta fields.
