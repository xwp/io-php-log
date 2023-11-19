<?php

namespace XWP\Log\Channels;

use XWP\Log\Formatters;
use XWP\Log\Configs\Email as EmailConfig;
use XWP\Log\Contracts\Channel;
use XWP\Log\Contracts\Formattable;

/**
 * Email logger channel, sends the email with the error to specific emails..
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Email implements Channel {
    /**
     * Email Channel Constructor.
     *
     * @param EmailConfig      $config  Channel's configuration.
     * @param Formattable|null $subject Optional. The subject formatter instance.
     * @param Formattable|null $body    Optional. The body formatter instance.
     */
    public function __construct(
        protected EmailConfig  $config,
        protected ?Formattable $subject = null,
        protected ?Formattable $body = null
    ) {
        $this->subject ??= new Formatters\Emails\Subject();
        $this->body    ??= new Formatters\Emails\Body();
    }

    /**
     * Distributes the message via e-mail.
     * It's a simple implementation that sends the email to the configured emails.
     *
     * @param string             $level   The level of the log message to store.
     * @param string|\Stringable $message The message to store.
     * @param array              $context The context.
     *
     * @return void
     */
    public function distribute( string $level, \Stringable|string $message, array $context = [] ): void {
        if ( ! $this->authorize() ) {
            return;
        }

        if ( ! empty( $this->config->levels ) && ! in_array( $level, $this->config->levels, true ) ) {
            return;
        }

        $subject = $this->subject->format( $level, $message, $context );
        $body    = $this->body->format( $level, $message, $context );

        $this->send( $subject, $body );
    }

    /**
     * Sends the email to the configured emails.
     *
     * @param string $subject   The subject of the email.
     * @param string $formatted The formatted body message.
     *
     * @return void
     */
    protected function send( string $subject, string $formatted ): void {
        foreach ( $this->config->emails as $email ) {
            wp_mail( $email, $subject, $formatted );
        }
    }

    /**
     * Checks if logs can be distributed via email.
     *
     * @return bool
     */
    protected function authorize(): bool {
        /**
         * Filters whether the email can be sent.
         * Helpful when you want to disable sending emails for some reason.
         *
         * @param bool        $can_send_emails Whether the email can be sent.
         * @param EmailConfig $config          The email configuration.
         *
         * @since 0.0.1
         *
         * @return bool
         */
        return (bool) apply_filters( 'xwp_log_channels_email_authorized', true, $this->config );
    }
}
