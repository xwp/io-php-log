<?php

namespace XWP\Log\Contracts;

/**
 * Channel, defines the interface for the log instance that is able to distribute a log message somewhere:
 * - File
 * - Database
 * - Slack
 * - Email
 * - CLI
 * - SMS
 * - ...
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
interface Channel {
	/**
	 * Distributes the log message.
	 *
	 * @param string             $level   The level of the log message to store.
	 * @param string|\Stringable $message The message to store.
	 * @param array              $context The context.
	 *
	 * @return void
	 */
	public function distribute( string $level, string|\Stringable $message, array $context = [] ): void;
}
