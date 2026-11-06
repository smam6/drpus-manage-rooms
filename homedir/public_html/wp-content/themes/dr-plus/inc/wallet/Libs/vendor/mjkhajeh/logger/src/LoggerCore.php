<?php

namespace MJ;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

final class LoggerCore implements LoggerInterface {
	private const MAX_BYTES = 10485760;
	private const LOG_FILE_NAME = 'log.txt';
	private const SENSITIVE_KEY_PATTERN = '/pass(word)?|pwd|secret|token|api[_-]?key|auth|authorization|cookie|session|bearer|private|signature|credit|card|cvv|cvc|ssn/i';

	private string $logFile;

	public function __construct( ?string $path = null ) {
		$this->logFile = $path ?: self::defaultPath();
	}

	public static function defaultPath(): string {
		if ( defined( 'ABSPATH' ) && is_string( ABSPATH ) ) {
			return rtrim( ABSPATH, '/\\' ) . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;
		}

		return rtrim( dirname( __DIR__, 4 ), '/\\' ) . DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;
	}

	public function emergency( $message, array $context = [] ) {
		$this->log( LogLevel::EMERGENCY, $message, $context );
	}

	public function alert( $message, array $context = [] ) {
		$this->log( LogLevel::ALERT, $message, $context );
	}

	public function critical( $message, array $context = [] ) {
		$this->log( LogLevel::CRITICAL, $message, $context );
	}

	public function error( $message, array $context = [] ) {
		$this->log( LogLevel::ERROR, $message, $context );
	}

	public function warning( $message, array $context = [] ) {
		$this->log( LogLevel::WARNING, $message, $context );
	}

	public function notice( $message, array $context = [] ) {
		$this->log( LogLevel::NOTICE, $message, $context );
	}

	public function info( $message, array $context = [] ) {
		$this->log( LogLevel::INFO, $message, $context );
	}

	public function debug( $message, array $context = [] ) {
		$this->log( LogLevel::DEBUG, $message, $context );
	}

	public function log( $level, $message, array $context = [] ) {
		$level = $this->normalizeLevel( $level );

		$source = 'unknown';
		if ( isset( $context['source'] ) && is_string( $context['source'] ) && $context['source'] !== '' ) {
			$source = $context['source'];
			unset( $context['source'] );
		}
		if ( $source === 'unknown' ) {
			$source = $this->detectSource();
		}

		$context = $this->sanitizeContext( $context );
		$message = $this->normalizeMessage( $message );
		$message = $this->interpolate( $message, $context );

		$timestamp = $this->timestamp();
		$entry = $this->formatEntry( $timestamp, $level, $message, $context, $source );

		$this->write( $entry );
	}

	private function normalizeLevel( $level ): string {
		if ( ! is_string( $level ) ) {
			throw new \InvalidArgumentException( 'Log level must be a string.' );
		}

		$level = strtolower( trim( $level ) );
		$valid = [
			LogLevel::EMERGENCY,
			LogLevel::ALERT,
			LogLevel::CRITICAL,
			LogLevel::ERROR,
			LogLevel::WARNING,
			LogLevel::NOTICE,
			LogLevel::INFO,
			LogLevel::DEBUG,
		];

		if ( ! in_array( $level, $valid, true ) ) {
			throw new \InvalidArgumentException( 'Invalid log level: ' . $level );
		}

		return $level;
	}

	private function normalizeMessage( $message ): string {
		if ( is_string( $message ) ) {
			$normalized = $message;
		} elseif ( is_object( $message ) && method_exists( $message, '__toString' ) ) {
			$normalized = (string) $message;
		} else {
			$normalized = $this->encodeJson( $message );
		}

		$normalized = trim( str_replace( ["\r", "\n"], ' ', $normalized ) );
		return $normalized === '' ? '-' : $normalized;
	}

	private function interpolate( string $message, array $context ): string {
		if ( strpos( $message, '{' ) === false ) {
			return $message;
		}

		$replace = [];
		foreach ( $context as $key => $value ) {
			if ( ! is_string( $key ) ) {
				continue;
			}
			$replace[ '{' . $key . '}' ] = $this->stringify( $value );
		}

		return strtr( $message, $replace );
	}

	private function sanitizeContext( array $context ): array {
		$sanitized = [];
		foreach ( $context as $key => $value ) {
			$sanitized[ $key ] = $this->sanitizeValue( $value, 0, is_string( $key ) ? $key : null );
		}
		return $sanitized;
	}

	private function sanitizeValue( $value, int $depth, ?string $key = null ) {
		if ( $key !== null && $this->isSensitiveKey( $key ) ) {
			return '[REDACTED]';
		}

		if ( $depth > 6 ) {
			return '[DEPTH-LIMIT]';
		}

		if ( is_array( $value ) ) {
			$sanitized = [];
			foreach ( $value as $childKey => $childValue ) {
				$sanitized[ $childKey ] = $this->sanitizeValue(
					$childValue,
					$depth + 1,
					is_string( $childKey ) ? $childKey : null
				);
			}
			return $sanitized;
		}

		if ( $value instanceof Throwable ) {
			return [
				'type' => get_class( $value ),
				'message' => $value->getMessage(),
				'code' => $value->getCode(),
				'file' => $value->getFile(),
				'line' => $value->getLine(),
			];
		}

		if ( is_object( $value ) ) {
			if ( $value instanceof \JsonSerializable ) {
				return $this->sanitizeValue( $value->jsonSerialize(), $depth + 1 );
			}
			if ( method_exists( $value, '__toString' ) ) {
				return (string) $value;
			}
			return '[OBJECT ' . get_class( $value ) . ']';
		}

		if ( is_resource( $value ) ) {
			return '[RESOURCE]';
		}

		return $value;
	}

	private function isSensitiveKey( string $key ): bool {
		return (bool) preg_match( self::SENSITIVE_KEY_PATTERN, $key );
	}

	private function stringify( $value ): string {
		if ( $value === null ) {
			return 'null';
		}
		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}
		if ( is_scalar( $value ) ) {
			return (string) $value;
		}
		return $this->encodeJson( $value );
	}

	private function detectSource(): string {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 12 );
		foreach ( $trace as $frame ) {
			$class = $frame['class'] ?? null;
			$function = $frame['function'] ?? null;

			if ( $class === __CLASS__ || $class === Logger::class ) {
				continue;
			}

			if ( $class ) {
				return $function ? $class . '::' . $function : $class;
			}

			if ( $function && $function !== 'call_user_func' && $function !== 'call_user_func_array' ) {
				return $function;
			}
		}

		return 'unknown';
	}

	private function timestamp(): string {
		if ( function_exists( 'wp_date' ) ) {
			return wp_date( 'Y-m-d H:i:s' );
		}

		$timezone = $this->resolveTimezone();
		$date = new DateTimeImmutable( 'now', $timezone );

		return $date->format( 'Y-m-d H:i:s' );
	}

	private function resolveTimezone(): DateTimeZone {
		if ( function_exists( 'wp_timezone' ) ) {
			$timezone = wp_timezone();
			if ( $timezone instanceof DateTimeZone ) {
				return $timezone;
			}
		}

		if ( function_exists( 'wp_timezone_string' ) ) {
			$timezoneString = wp_timezone_string();
			if ( $timezoneString ) {
				try {
					return new DateTimeZone( $timezoneString );
				} catch ( \Exception $e ) {
					// Fall through to default timezone.
				}
			}
		}

		$defaultTimezone = date_default_timezone_get();
		if ( is_string( $defaultTimezone ) && $defaultTimezone !== '' ) {
			try {
				return new DateTimeZone( $defaultTimezone );
			} catch ( \Exception $e ) {
				// Fall back to UTC below.
			}
		}

		return new DateTimeZone( 'UTC' );
	}

	private function formatEntry( string $timestamp, string $level, string $message, array $context, string $source ): string {
		$contextJson = $this->encodeJson( $context );

		return sprintf(
			'%s [%s] %s | source=%s | context=%s%s',
			$timestamp,
			$level,
			$message,
			$source,
			$contextJson,
			PHP_EOL
		);
	}

	private function encodeJson( $value ): string {
		$json = json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR );
		return $json !== false ? $json : '{}';
	}

	private function write( string $entry ): void {
		$dir = dirname( $this->logFile );
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0755, true );
		}

		$handle = @fopen( $this->logFile, 'c+' );
		if ( $handle === false ) {
			return;
		}

		try {
			if ( ! flock( $handle, LOCK_EX ) ) {
				return;
			}

			$this->enforceLimit( $handle, strlen( $entry ) );

			fseek( $handle, 0, SEEK_END );
			fwrite( $handle, $entry );
			fflush( $handle );
			flock( $handle, LOCK_UN );
		} finally {
			fclose( $handle );
		}
	}

	private function enforceLimit( $handle, int $incomingBytes ): void {
		$stats = fstat( $handle );
		$current = $stats['size'] ?? 0;

		if ( $current + $incomingBytes <= self::MAX_BYTES ) {
			return;
		}

		ftruncate( $handle, 0 );
		rewind( $handle );
	}
}
