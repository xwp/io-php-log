<?php

namespace XWP\Log\Contracts;

/**
 * Composite, defines the interface for the Log's pieces to be composited in one place and behave as one unit.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
interface Composite {
	/**
	 * Returns what is composites of.
	 *
	 * @return array
	 */
	public function provides(): array;
}
