<?php

namespace XWP\Log\Configs;

use XWP\Log\LogLevel;

/**
 * Email Configuration Instance.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Email {
    /**
     * @param array<non-empty-string> $emails The emails to send the error to.
     * @param string[]                $levels Optional. The log levels that need to be sent out.
     *                                        The levels are: emergency, alert, critical, error, warning, notice, info, debug.
     *                                        Keep it empty to send for all.
     */
    public function __construct(
        public array $emails,
        public array $levels = [ LogLevel::ERROR, LogLevel::EMERGENCY ]
    ) {
        foreach ( $this->emails as $email ) {
            $this->ensure_email( $email );
        }
    }

    /**
     * Ensures that emails are valid.
     *
     * @param string $email The email to check.
     *
     * @return void
     */
    protected function ensure_email( string $email ): void {
        if ( ! is_email( $email ) ) {
            throw new \InvalidArgumentException( sprintf( 'The email `%s` is not valid.', $email ) );
        }
    }
}
