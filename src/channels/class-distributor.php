<?php

namespace XWP\Log\Channels;

use XWP\Log\Contracts\Channel;
use XWP\Log\Contracts\Composite as CompositeContract;

/**
 * Distributor is a composite of channels that allows to distribute log messages into multiple places at the same time.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Distributor implements Channel, CompositeContract {
    /**
     * The channels to distribute the log message.
     *
     * @var Channel[]
     */
    protected array $channels;

    /**
     * Channels Composite constructor.
     *
     * @param Channel ...$channels The channels to distribute the log message.
     */
    public function __construct( Channel ...$channels ) {
        $this->channels = $channels;
    }

    /**
     * Distributes the log message.
     *
     * @param string             $level   The type of the log message to store.
     * @param string|\Stringable $message The message to store.
     * @param array              $context The context.
     *
     * @return void
     */
    public function distribute( string $level, \Stringable|string $message, array $context = [] ): void {
        foreach ( $this->provides() as $storage ) {
            try {
                $storage->distribute( $level, $message, $context );
            } catch ( \Throwable $e ) {
                error_log(
                    sprintf( 'Failed to distribute the log message into the channel `%s`, due to: %s', class_basename( $storage ), $e->getMessage() ),
                    E_USER_ERROR
                );
            }
        }
    }

    /**
     * Returns all available channels.
     *
     * @return Channel[]
     */
    public function provides(): array {
        return $this->channels;
    }
}
