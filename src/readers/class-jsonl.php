<?php

namespace XWP\Log\Readers;

use Generator;
use XWP\Log\Contracts\FileReader;

/**
 * Support for reading JSONL files.
 *
 * @link https://jsonlines.org
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Jsonl implements FileReader {
	/**
	 * Reads the jsonl file, line by line, for more efficient memory usage.
	 *
	 * @param string $file The file to read.
	 *
	 * @return Generator
	 */
	public function read( string $file ): Generator {
		if ( ! file_exists( $file ) ) {
			return ( yield null );
		}

		$stream = $file;

		$close_stream = false;

		// Open file pointer if `$file` is not a resource.
		if ( ! is_resource( $file ) ) {
			$stream = fopen( $file, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

			if ( ! $stream ) {
				return ( yield null );
			}

			$close_stream = true;
		}

		while ( ( $row = fgets( $stream ) ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			// Skip invalid JSON and jump to the next line.
			if ( ! $this->is_json_valid( $row ) ) {
				continue;
			}

			// we serve from a jsonl file, so each line is a separate json.
			$record = json_decode( $row );

			// Using generator for memory saving and be able to read all records.
			$generator_value = ( yield $record );

			// Break the generator loop and go to close the file handle.
			if ( 'stop' === $generator_value ) {
				break;
			}
		}

		// Close an open file pointer if we've opened it.
		if ( $close_stream ) {
			fclose( $stream );
		}
	}

    /**
     * Checks whether the json is valid or not.
     *
     * @param string $json json as a raw format.
     *
     * @return bool
     */
    protected function is_json_valid( string $json ): bool {
        return json_decode( $json ) !== null && json_last_error() === JSON_ERROR_NONE;
    }
}
