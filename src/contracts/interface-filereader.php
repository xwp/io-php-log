<?php

namespace XWP\Log\Contracts;

use Generator;

/**
 * FileReader, defines the interface for instances that can read files.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
interface FileReader {
	/**
	 * Reads the file.
	 *
	 * @param string $file The file to read.
	 *
	 * @return Generator
	 */
	public function read( string $file ): Generator;
}
