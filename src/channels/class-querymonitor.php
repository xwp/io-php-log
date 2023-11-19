<?php

namespace XWP\Log\Channels;

use XWP\Log\Contracts\Channel;

/**
 * Query Monitor logger channel, wrapper that helps track given data and pass it into Query Monitor Plugin.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class QueryMonitor implements Channel {
	/**
	 * Distributes the log message into Query's monitor output.
	 *
	 * @param string $level               The level of the log message to store.
	 * @param string|\Stringable $message The message to store.
	 * @param array              $context The context.
	 *
	 * @return void
	 */
	public function distribute( string $level, \Stringable|string $message, array $context = [] ): void {
		if ( ! class_exists( '\\QM' ) ) {
			return;
		}

		$available_types = get_class_methods( '\\QM' );

		// Make sure we only pass the available types, if not use the default one.
		if ( ! in_array( $level, $available_types, true ) ) {
			$level = 'log';
		}

		call_user_func( [ '\\QM', $level ], $message, $context );
	}
}
