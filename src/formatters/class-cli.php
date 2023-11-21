<?php

namespace XWP\Log\Formatters;

use XWP\Log\Contracts\Formattable;
use XWP\Log\LogLevel;

/**
 * CLI Formatter, formats the message for CLI STDOUT.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Cli implements Formattable {
    /**
     * Formats the log message for CLI STDOUT output adding level color.
     *
     * @param string             $level   Type/level of the error.
     * @param string|\Stringable $message The log message.
     * @param array              $context The log context.
     *
     * @return string
     */
    public function format( string $level, \Stringable|string $message, array $context = [] ): string {

        $label = strtoupper( $level );

        if ( class_exists( '\\WP_CLI' ) ) {

            $color = match ( $level ) {
                LogLevel::INFO => '%G',    // ['color' => 'green', 'style' => 'bright'],
                LogLevel::NOTICE => '%Y',  // ['color' => 'yellow', 'style' => 'bright'],
                LogLevel::WARNING => '%C', // ['color' => 'cyan', 'style' => 'bright'],
                LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::EMERGENCY, LogLevel::ERROR => '%R', // ['color' => 'red', 'style' => 'bright'],
                default => '',
            };

            $label = \WP_CLI::colorize( "$color$label:%n" );
        } else {
            $label = "$label:";
        }

        return sprintf( '%s %s context: %s' . PHP_EOL, $label, $message . wp_json_encode( $context ) );
    }
}
