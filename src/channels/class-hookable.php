<?php

namespace XWP\Log\Channels;

use XWP\Log\Contracts\Channel;

/**
 * Hookable channel, distribute the message by emitting an action that other modules could listen and do their stuff.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Hookable implements Channel {
	/**
	 * Distribute the message by emitting an action that other modules could listen and do their stuff.
	 *
	 * @param string $level               The type of the log message to store.
	 * @param string|\Stringable $message The message to store.
	 * @param array $context              The context.
	 *
	 * @return void
	 */
	public function distribute( string $level, \Stringable|string $message, array $context = [] ): void {
		/**
		 * Fire an action that other modules could listen and do their stuff.
		 *
		 * @param string|\Stringable $message Message that is being logged.
		 * @param array $context              Message context.
		 * @param string $level               One of the log types: `error`, `warning`, `notice`, `info`, `emergency`, `debug`, `critical`.
		 *
		 * @since 0.0.1
		 */
		do_action( 'xwp_log_distribute', $message, $context, $level );

		/**
		 * Fire an action that other modules could listen and do their stuff.
		 *
		 * Dynamic part is a level type of the log information: `error`, `warning`, `notice`, `info`, `emergency`, `debug`, `critical`.
		 *
		 * @param string|\Stringable $message Message that is being logged.
		 * @param array $context              Message context.
		 *
         * @since 0.0.1
		 */
		do_action( 'xwp_log_distribute_' . $level, $message, $context );
	}
}
