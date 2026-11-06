<?php
namespace DrPlus;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logger {
	PRIVATE STATIC $LOGGER = null;
	PRIVATE STATIC $LOGGER_NAME = '';

	public static function init() {
		$logger_name = 'drplus';
		if( $logger_name == self::$LOGGER_NAME && self::$LOGGER !== null ) return;

		// Config formatter
		$date_format = "Y-m-d H:i:s";
		$output = "[%datetime%] | \"%channel%\" - \"%level_name%\": %message% - %context%\n";
		$formatter = new LineFormatter( $output, $date_format );

		// Config handler
		$handler = new StreamHandler( DRPLUS_DIR . 'log.txt', MonologLogger::ERROR );
		$handler->setFormatter( $formatter );

		// Create instance of logger
		$logger = new MonologLogger( $logger_name );
		$logger->pushHandler( $handler );

		// Deny access to log file
		if( !DRPLUS_IS_LOCAL && file_exists( DRPLUS_DIR . 'log.txt' ) ) {
			@chmod( DRPLUS_DIR . 'log.txt', 0600 );
		}

		self::$LOGGER = $logger;
		self::$LOGGER_NAME = $logger_name;
	}

	public static function warning( $text, $extra = [] ) {
		if( self::$LOGGER === null ) return;

		self::$LOGGER->warning( $text, $extra );
	}

	public static function error( $text, $extra = [] ) {
		if( self::$LOGGER === null ) return;

		self::$LOGGER->error( $text, $extra );
	}

	public static function critical( $text, $extra = [] ) {
		if( self::$LOGGER === null ) return;

		self::$LOGGER->critical( $text, $extra );
	}
}