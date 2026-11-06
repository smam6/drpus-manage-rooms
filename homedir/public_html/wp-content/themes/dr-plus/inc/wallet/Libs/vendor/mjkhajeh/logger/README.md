# MJ Logger

Lightweight, WordPress-friendly logging library for themes and plugins.

## Features
- PSR-3 compatible API (levels and method names).
- Safe context handling with sensitive value redaction.
- Message interpolation using `{key}` placeholders.
- Automatic source detection for easier tracing.
- WordPress-aware timestamp and timezone handling.
- Simple file-based logging with size limit and locking.

## Requirements
- PHP 7.4 or higher.
- `psr/log` (declared in `composer.json`).

## Installation
Install via Composer:

```bash
composer require mj/logger
```

## Quick Start
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use MJ\Logger;

// Optional: set a custom log file path.
Logger::init(__DIR__ . '/storage/logs/app.log');

Logger::info('Plugin loaded', [
    'source' => 'my-plugin',
    'version' => '1.2.3'
]);
```

## API Reference
This package exposes two main classes:
- `MJ\Logger` is a static facade for a shared logger instance.
- `MJ\LoggerCore` is the PSR-3 compatible implementation.

### MJ\Logger
Use this when you want a simple, shared logger.

#### `Logger::init(?string $path = null): void`
Initializes the shared logger instance, optionally with a custom log file path.

```php
use MJ\Logger;

Logger::init(WP_CONTENT_DIR . '/logs/mj.log');
```

#### `Logger::build(?string $path = null): LoggerCore`
Creates a new independent logger instance.

```php
use MJ\Logger;

$logger = Logger::build(__DIR__ . '/logs/custom.log');
$logger->info('Independent logger instance');
```

#### `Logger::core(): LoggerCore`
Returns the shared logger instance (creates it lazily if needed).

```php
use MJ\Logger;

$logger = Logger::core();
$logger->debug('Using the shared core instance');
```

#### Logging methods
These proxy to the shared instance. Each method accepts a message and a context array.

#### `Logger::emergency($message, array $context = [])`
```php
Logger::emergency('Site is down', ['source' => 'health-check']);
```

#### `Logger::alert($message, array $context = [])`
```php
Logger::alert('Immediate attention required', ['ticket' => 42]);
```

#### `Logger::critical($message, array $context = [])`
```php
Logger::critical('Critical failure in checkout flow');
```

#### `Logger::error($message, array $context = [])`
```php
Logger::error('Database connection failed', ['host' => 'db1']);
```

#### `Logger::warning($message, array $context = [])`
```php
Logger::warning('Cache warmup took longer than expected', ['seconds' => 8.2]);
```

#### `Logger::notice($message, array $context = [])`
```php
Logger::notice('Deprecated option used', ['option' => 'legacy_mode']);
```

#### `Logger::info($message, array $context = [])`
```php
Logger::info('Plugin initialized', ['version' => '2.0.0']);
```

#### `Logger::debug($message, array $context = [])`
```php
Logger::debug('Debug payload', ['payload' => ['a' => 1, 'b' => 2]]);
```

#### `Logger::log($level, $message, array $context = [])`
```php
Logger::log('warning', 'Custom level usage', ['source' => 'my-plugin']);
```

### MJ\LoggerCore
Implements `Psr\Log\LoggerInterface` and contains all core logic.

#### `__construct(?string $path = null)`
Creates a new logger instance with an optional custom log path.

```php
use MJ\LoggerCore;

$core = new LoggerCore(__DIR__ . '/logs/core.log');
$core->info('Core instance ready');
```

#### `LoggerCore::defaultPath(): string`
Returns the default log path.

```php
use MJ\LoggerCore;

$defaultPath = LoggerCore::defaultPath();
```

#### Logging methods
LoggerCore implements all PSR-3 methods with the same signatures:
- `emergency($message, array $context = [])`
- `alert($message, array $context = [])`
- `critical($message, array $context = [])`
- `error($message, array $context = [])`
- `warning($message, array $context = [])`
- `notice($message, array $context = [])`
- `info($message, array $context = [])`
- `debug($message, array $context = [])`
- `log($level, $message, array $context = [])`

Example with LoggerCore:
```php
use MJ\LoggerCore;

$core = new LoggerCore();
$core->warning('Running a scheduled task', ['task' => 'daily_cleanup']);
```

## Context and Message Formatting
- If the message is a string, it is used as-is.
- If the message is an object with `__toString()`, it is cast to string.
- Otherwise, the message is JSON-encoded.
- Newlines are replaced with spaces and empty messages become `-`.

### Interpolation
Placeholders in the message are replaced from the context:

```php
Logger::info('User {id} logged in', ['id' => 123]);
```

### Sensitive Data Redaction
Context keys matching any of these patterns are redacted as `[REDACTED]` (case-insensitive):
`pass`, `password`, `pwd`, `secret`, `token`, `api_key`, `api-key`, `auth`, `authorization`, `cookie`, `session`, `bearer`, `private`, `signature`, `credit`, `card`, `cvv`, `cvc`, `ssn`.

### Depth Limit
Nested context arrays are limited to 6 levels deep. Deeper entries are replaced with `[DEPTH-LIMIT]`.

## Source Detection
- If `context['source']` is provided, it is used.
- Otherwise, the logger inspects the stack trace and uses the first external caller.

## Log Format
Each line is written as:
```
YYYY-mm-dd HH:MM:SS [level] message | source=... | context=...
```

Example:
```
2026-02-11 14:30:12 [info] Plugin initialized | source=my-plugin | context={"version":"2.0.0"}
```

## File Handling
- Default filename is `log.txt`.
- In WordPress, the default path is `ABSPATH/log.txt`.
- Outside WordPress, the path falls back to four directories above `src/`.
- The log file directory is created if missing (`0755`).
- Writes are protected with file locks.
- Maximum file size is 10 MB; if exceeded, the file is truncated.

## WordPress Notes
- Uses `wp_date()` and `wp_timezone()` when available.
- Works well in themes and plugins with Composer autoloading.

## PSR-3 Polyfill
This repo includes a lightweight PSR-3 polyfill at `src/PsrLog.php` and autoloads it via `composer.json`.
- If you always install `psr/log`, you can remove the polyfill and the `autoload.files` entry.
- If you need to run without Composer or without `psr/log`, keep it as-is.

## License
MIT. See `LICENSE`.
