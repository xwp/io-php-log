<?php

namespace XWP\Log\Contracts;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Logger interface to standardise the loggers.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
interface Logger extends PsrLoggerInterface {
    /**
     * Sets the logger group and populates the group context among other group messages.
     *
     * @param string $group   The group name.
     * @param array  $context The group context.
     *
     * @return $this
     */
    public function start_group( string $group, array $context = [] ): static;

    /**
     * Ends the group.
     *
     * @return $this
     */
    public function end_group(): static;

    /**
     * Sets the shared context of the logger.
     *
     * @param array $context The shared context.
     *
     * @return $this
     */
    public function share( array $context ): static;
}
