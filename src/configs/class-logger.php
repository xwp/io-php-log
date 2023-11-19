<?php

namespace XWP\Log\Configs;

/**
 * Logger configuration.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Logger {
    public function __construct(
        public string $group_separator = ':'
    ) {

    }
}
