<?php

namespace XWP\Log\Contracts;

/**
 * Formattable, defines the interface for a formatter instance.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
interface Formattable {
	/**
	 * Formats the log message.
	 *
	 * @param string             $level   Type/level of the error.
	 * @param string|\Stringable $message The log message.
	 * @param array              $context The log context.
	 *
	 * @return string
	 */
	public function format( string $level, string|\Stringable $message, array $context = [] ): string;
}
