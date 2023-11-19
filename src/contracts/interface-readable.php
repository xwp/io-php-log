<?php

namespace XWP\Log\Contracts;

use Generator;

/**
 * Readable, defines the interface for the Log instance that is able to read stored log messages.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
interface Readable {
	/**
	 * Reads the file contents.
	 *
	 * @param FileReader $reader To read file contents.
	 *
	 * @return Generator
	 */
	public function read( FileReader $reader ): Generator;
}
