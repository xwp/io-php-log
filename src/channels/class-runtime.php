<?php

namespace XWP\Log\Channels;

use XWP\Log\Formatters;
use XWP\Log\Contracts\Channel;
use XWP\Log\Contracts\Formattable;

/**
 * Runtime Channel, allows to store logs into an environment runtime log file.
 * Basically on local environment, this will write to the `debug.log` file.
 * On VIP, it goes into runtime logs.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Runtime implements Channel {
	/**
	 * Runtime Channel constructor.
	 *
	 * @param Formattable|null $formatter Optional. The formatter instance.
	 */
	public function __construct( protected ?Formattable $formatter = null ) {
		$this->formatter ??= new Formatters\Base();
	}

	/**
	 * Distributes the message into an environment runtime log file.
	 *
	 * @param string             $level   The level of the log message to store.
	 * @param string|\Stringable $message The message to store.
	 * @param array              $context The context.
	 *
	 * @return void
	 */
	public function distribute( string $level, \Stringable|string $message, array $context = [] ): void {
		$this->write( $this->formatter->format( $level, $message, $context ) );
	}

	/**
	 * Writes into the runtime log file.
	 *
	 * @param string $formatted The formatted message.
	 *
	 * @return void
	 */
	protected function write( string $formatted ): void {
		error_log( $formatted );
	}
}
