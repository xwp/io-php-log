<?php

namespace XWP\Log;

use XWP\Log\Contracts\Channel;
use XWP\Log\Configs\Logger as LoggerConfig;
use XWP\Log\Contracts\Logger as LoggerContract;

/**
 * Logger defines the base logger functionality.
 * It's fully compatible with the PSR-3 logger interface.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Logger implements LoggerContract {
    /**
     * Defines the logger group information.
     *
     * @var array{prefix: string[], context: array[]}
     */
    protected array $group = [
        'prefix'  => [],
        'context' => [],
    ];

    /**
     * Defines the shared context of the logger.
     *
     * @var array
     */
    protected array $shared_context = [];

    public function __construct(
        protected Channel $channel,
        protected ?LoggerConfig $config = null
    ) {
        $this->config ??= new LoggerConfig();
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed                                  $level   Type/level of the error.
     * @param string|\Stringable|\WP_Error\Throwable $message The log message.
     * @param array                                  $context The log context.
     *
     * @return void
     */
    public function log( $level, $message, array $context = [] ): void {
        if ( $message instanceof \Stringable ) {
            $message = (string) $message;
        }

        $context = $this->prepare_context( $context );

        if ( $message instanceof \WP_Error ) {

            foreach ( $message->get_error_messages() as $error_message ) {
                $this->channel->distribute( $level, $this->prepare_message( $error_message ), $context );
            }

            return;
        }

        if ( $message instanceof \Throwable ) {
            $message = $message->getMessage();
        }

        $this->channel->distribute( $level, $this->prepare_message( $message ), $context );
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string|\Stringable|\WP_Error|\Throwable $message The log message.
     * @param mixed[]                                 $context The log context.
     */
    public function warning( $message, array $context = [] ): void {
        $this->log( LogLevel::WARNING, $message, $context );
    }

    /**
     * Normal but significant events.
     *
     * @param string|\Stringable|\WP_Error|\Throwable $message The log message.
     * @param mixed[]                                 $context The log context.
     */
    public function notice( $message, array $context = [] ): void {
        $this->log( LogLevel::NOTICE, $message, $context );
    }

    /**
     * Adds a log record at the info level.
     *
     * @param string|\Stringable|\WP_Error|\Throwable $message The log message.
     * @param mixed[]                                 $context The log context.
     */
    public function info( $message, array $context = [] ): void {
        $this->log( LogLevel::INFO, $message, $context );
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string|\Stringable|\WP_Error|\Throwable $message The log message.
     * @param mixed[]                                 $context The log context.
     */
    public function error( $message, array $context = [] ): void {
        $this->log( LogLevel::ERROR, $message, $context );
    }

    /**
     * System is unusable.
     *
     * @param string|\Stringable|\WP_Error|\Throwable $message The log message.
     * @param mixed[]                                 $context The log context.
     */
    public function emergency( $message, array $context = [] ): void {
        $this->log( LogLevel::EMERGENCY, $message, $context );
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     *
     * @param string|\Stringable|\WP_Error|\Throwable $message The log message.
     * @param mixed[]                                 $context The log context.
     */
    public function alert( $message, array $context = [] ): void {
        $this->log( LogLevel::ALERT, $message, $context );
    }

    /**
     * Detailed debug information.
     *
     * @param string|\Stringable|\WP_Error|\Throwable $message The log message.
     * @param mixed[]                                 $context The log context.
     */
    public function debug( $message, array $context = [] ): void {
        $this->log( LogLevel::DEBUG, $message, $context );
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string|\Stringable|\WP_Error|\Throwable $message The log message.
     * @param mixed[]                                 $context The log context.
     */
    public function critical( $message, array $context = [] ): void {
        $this->log( LogLevel::CRITICAL, $message, $context );
    }

    /**
     * Sets the shared context of the logger, all logs get this context.
     *
     * @param array $context The shared context.
     *
     * @return $this
     */
    public function share( array $context ): static {
        $this->shared_context = $context;

        return $this;
    }

    /**
     * Sets the group for log messages.
     * All messages will be prefixed with the group name.
     * The group context will be merged with the shared context and shared with the current log record.
     *
     * Example:
     *  $logger->start_group( 'Group Name', [ 'group' => 'context' ] ); // <| this will populate logs with the group prefix and shared context.
     *  $logger->info( 'message' ); // <| this will be `Group Name: message` and will get the context `[ 'group' => 'context' ]` as well.
     *  $logger->end_group(); // <| this ends the group, removes the prefix and shared context.
     *
     * @param string $group   The group name.
     * @param array  $context The group context.
     *
     * @return $this
     */
    public function start_group( string $group, array $context = [] ): static {
        $this->group['prefix'][]  = $group;
        $this->group['context'][] = $context;

        return $this;
    }

    /**
     * Ends the latest started group.
     *
     * @return $this for chaining.
     */
    public function end_group(): static {
        // End the group level.
        array_pop( $this->group['prefix'] );
        array_pop( $this->group['context'] );

        return $this;
    }

    /**
     * Returns the group message if the logger is being grouped.
     *
     * @param string $message The message to group.
     *
     * @return string Prefixed message with the latest started group name.
     */
    protected function prepare_message( string $message ): string {
        if ( ! empty( $this->group['prefix'] ) ) {
            // Takes the last group prefix and adds to the message with a separator.
            $message = implode(
                $this->config->group_separator,
                array_filter( [
                    end( $this->group['prefix'] ) ?: '',
                    $message,
                ] )
            );
        }

        return $message;
    }

    /**
     * Returns the group context if the logger is being grouped.
     *
     * @param array $context The context to merge with the group context.
     *
     * @return array The merged context.
     */
    protected function prepare_context( array $context ): array {
        // Merge the whole shared context with the current group.
        if ( ! empty( $this->group['context'] ) ) {
            return array_merge(
            // Merges the shared context with the current group context.
                array_merge( $this->shared_context, ...$this->group['context'] ),
                // Merges the current context with the shared/grouped context.
                $context
            );
        }

        return array_merge( $this->shared_context, $context );
    }
}
