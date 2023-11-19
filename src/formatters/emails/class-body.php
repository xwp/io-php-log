<?php

namespace XWP\Log\Formatters\Emails;

use XWP\Log\Contracts\Formattable;

/**
 * Formats the message into an email body string.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Body implements Formattable {
    /**
     * Formats the message into an email body.
     *
     * @param string             $level   The level of the log message to format.
     * @param string|\Stringable $message The message to format.
     * @param array              $context The context log messages come along.
     *
     * @return string The formatted message. Example:
     */
    public function format( string $level, \Stringable|string $message, array $context = [] ): string {
        return implode( ' ', [
            strtoupper( wp_get_environment_type() ) . '.' . strtoupper( $level ),
            $message,
            "\r\n\r\n --- Context --- \r\n\r\n",
            wp_json_encode( $context ),
            "\r\n\r\n --- Trace --- \r\n\r\n",
            $this->generate_trace( $context ),
        ] );
    }

    /**
     * Generates a trace.
     *
     * @param array $context The context log messages come along.
     *
     * @return string The trace where the log message was generated.
     */
    protected function generate_trace( array $context = [] ): string {
        /**
         * According to the PSR-3 spec, the exception key is reserved for use by the logger,
         * so we can take the trace from that exception instance.
         *
         * @link https://www.php-fig.org/psr/psr-3/#13-context
         */
        if ( ! empty( $context['exception'] ) && $context['exception'] instanceof \Throwable ) {
            return $context['exception']->getTraceAsString();
        }

        ob_start();

        debug_print_backtrace();

        $trace = ob_get_contents();

        ob_end_clean();

        // Prepares the method name that we can recognise it and remove it from the trace.
        // Example: XWP\Log\Formatters\Emails\Body::generate_trace => XWP\\Log\\Formatters\\Emails\\Body->generate_trace
        $method = wp_slash( str_replace('::', '->', __METHOD__ ) );

        // Remove first item from backtrace as it's this method which is redundant.
        $trace = preg_replace ('/^#0.+' . $method . "[^\n]*\n/", '', $trace, 1 );

        // Renumber backtrace items.
        return preg_replace_callback('/^#(\d+)/m', static fn( $matches ) => '#' . ( $matches[ 1 ] - 1 ), $trace );
    }
}
