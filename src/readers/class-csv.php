<?php

namespace XWP\Log\Readers;

use Generator;
use XWP\Log\Contracts\FileReader;

/**
 * Reads CSV files.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class Csv implements FileReader {
	/**
	 * Reads the csv file, line by line, for more efficient memory usage.
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

		$headers = fgetcsv( $stream );

		while ( ( $row = fgetcsv( $stream ) ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition

			// Set headings to values, wo we get headings as keys and can recognize the value.
			$record = array_combine( $headers, $row );

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
}
