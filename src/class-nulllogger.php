<?php

namespace XWP\Log;

use XWP\Log\Contracts\Channel;
use XWP\Log\Contracts\Logger as LoggerContract;

/**
 * Nullable logger class, defines the null logger functionality.
 * It does nothing, just distributes the logs into the void, by providing the complete interface of the logger.
 *
 * It's useful for testing purposes or in places where we have a logger instance as a dependency, but we actually don't want to log.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class NullLogger extends Logger implements LoggerContract {
    public function __construct() {
        parent::__construct( new class implements Channel {
            public function distribute( string $level, $message, array $context = [] ): void {
                // Do nothing, just skip any log record and let them get lost in the void.
            }
        } );
    }
}
