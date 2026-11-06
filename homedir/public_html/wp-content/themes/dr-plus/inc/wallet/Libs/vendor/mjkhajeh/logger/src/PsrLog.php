<?php

namespace Psr\Log {
	if ( ! interface_exists( __NAMESPACE__ . '\\LoggerInterface' ) ) {
		interface LoggerInterface {
			public function emergency( $message, array $context = [] );
			public function alert( $message, array $context = [] );
			public function critical( $message, array $context = [] );
			public function error( $message, array $context = [] );
			public function warning( $message, array $context = [] );
			public function notice( $message, array $context = [] );
			public function info( $message, array $context = [] );
			public function debug( $message, array $context = [] );
			public function log( $level, $message, array $context = [] );
		}
	}

	if ( ! class_exists( __NAMESPACE__ . '\\LogLevel' ) ) {
		final class LogLevel {
			public const EMERGENCY = 'emergency';
			public const ALERT = 'alert';
			public const CRITICAL = 'critical';
			public const ERROR = 'error';
			public const WARNING = 'warning';
			public const NOTICE = 'notice';
			public const INFO = 'info';
			public const DEBUG = 'debug';
		}
	}
}
