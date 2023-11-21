<?php

namespace XWP\Log\Channels;

use XWP\Log\Contracts\Channel;
use XWP\Log\Contracts\Formattable;
use XWP\Log\Formatters;

/**
 * CLI logger channel, wrapper that helps track given data and pass it into CLI STDOUT.
 *
 * @since 0.0.1
 *
 *
 * @package XWP\Log
 */
class CLI implements Channel {
    /**
     * CLI Channel constructor.
     *
     * @param Formattable|null $formatter Optional. The formatter instance.
     */
    public function __construct( protected ?Formattable $formatter = null ) {
        $this->formatter ??= new Formatters\Cli();
    }

    /**
     * Distributes the message into a CLI STDOUT.
     *
     * @param string             $level   The level of the log message to store.
     * @param string|\Stringable $message The message to store.
     * @param array              $context The context.
     *
     * @return void
     */
    public function distribute( string $level, \Stringable|string $message, array $context = [] ): void {
        if ( $this->authorize() ) {
            $this->write( $this->formatter->format( $level, $message, $context ) );
        }
    }

    /**
     * Writes into the runtime CLI log STDOUT.
     *
     * It might be discarded once the CLI ran with `--quite` flag.
     *
     * @param string $formatted The formatted message.
     *
     * @return void
     */
    protected function write( string $formatted ): void {
        \WP_CLI::log( $formatted );
    }

    /**
     * Checks if the CLI is available.
     *
     * @return bool
     */
    protected function authorize(): bool {
        return ( defined( 'WP_CLI' ) && WP_CLI ) && class_exists( '\\WP_CLI' );
    }
}
