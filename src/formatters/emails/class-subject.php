<?php

namespace XWP\Log\Formatters\Emails;

use XWP\Log\Contracts\Formattable;

/**
 * Formats the message into an email subject string.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Subject implements Formattable {
    /**
     * Formats the message into a subject string.
     *
     * @param string             $level   The level of the log message to format.
     * @param string|\Stringable $message The message to format.
     * @param array              $context The context log messages come along.
     *
     * @return string The formatted message. Example: `[Blog Name] PRODUCTION.ERROR`.
     */
    public function format( string $level, \Stringable|string $message, array $context = [] ): string {
        /*
		 * The blogname option is escaped with esc_html() on the way into the database in sanitize_option().
		 * We want to reverse this for the plain text arena of emails.
		 */
        $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

        return wp_specialchars_decode(
            sprintf(
                '[%1$s] %2$s.%3$s',
                $blogname,
                strtoupper( wp_get_environment_type() ),
                strtoupper( $level )
            )
        );
    }
}
