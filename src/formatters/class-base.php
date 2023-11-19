<?php

namespace XWP\Log\Formatters;

use XWP\Log\Contracts\Formattable;

/**
 * Formats the message into a simple string.
 * It's a simple base formatter.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Base implements Formattable {
    /**
     * Formats the message into a string.
     *
     * @param string             $level   The level of the log message to format.
     * @param string|\Stringable $message The message to format.
     * @param array              $context The context log messages come along.
     *
     * @return string The formatted message. Example: `production.ERROR Some message context: {"foo":"bar"}`
     */
    public function format( string $level, \Stringable|string $message, array $context = [] ): string {
        // Example: production.ERROR Some message context: {"foo":"bar"}
        return sprintf(
            '%1$s.%2$s %3$s context: %4%s' . PHP_EOL,
            wp_get_environment_type(),
            strtoupper( $level ),
            $message,
            wp_json_encode( $context )
        );
    }
}
