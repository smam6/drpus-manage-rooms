<?php

namespace MJ;

final class Logger {
	private static ?LoggerCore $core = null;

	public static function init( ?string $path = null ): void {
		self::$core = new LoggerCore( $path );
	}

	public static function build( ?string $path = null ): LoggerCore {
		return new LoggerCore( $path );
	}

	public static function core(): LoggerCore {
		if ( self::$core === null ) {
			self::$core = new LoggerCore();
		}
		return self::$core;
	}

	public static function emergency( $message, array $context = [] ) {
		self::core()->emergency( $message, $context );
	}

	public static function alert( $message, array $context = [] ) {
		self::core()->alert( $message, $context );
	}

	public static function critical( $message, array $context = [] ) {
		self::core()->critical( $message, $context );
	}

	public static function error( $message, array $context = [] ) {
		self::core()->error( $message, $context );
	}

	public static function warning( $message, array $context = [] ) {
		self::core()->warning( $message, $context );
	}

	public static function notice( $message, array $context = [] ) {
		self::core()->notice( $message, $context );
	}

	public static function info( $message, array $context = [] ) {
		self::core()->info( $message, $context );
	}

	public static function debug( $message, array $context = [] ) {
		self::core()->debug( $message, $context );
	}

	public static function log( $level, $message, array $context = [] ) {
		self::core()->log( $level, $message, $context );
	}
}
