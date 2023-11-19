<?php

namespace XWP\Log\Channels;

use Generator;
use XWP\Log\Formatters;
use XWP\Log\Contracts\Channel;
use XWP\Log\Contracts\FileReader;
use XWP\Log\Contracts\Formattable;
use XWP\Log\Contracts\Readable;

/**
 * File storage that allows to store logs into a file.
 *
 * @since 0.0.1
 *
 * @package XWP\Log
 */
class File implements Channel, Readable {
	/**
	 * File constructor.
	 *
	 * @param string           $filepath  The full path to the log file including the file name.
	 * @param Formattable|null $formatter Optional. The formatter to format the message.
	 */
	public function __construct(
        protected string $filepath,
        protected ?Formattable $formatter = null
    ) {
		$this->formatter ??= new Formatters\Base();
		$this->prepare_file_system();
	}

	/**
	 * Checks whether the file exists.
	 *
	 * @return bool
	 */
	public function exists(): bool {
		return file_exists( $this->filepath );
	}

	/**
	 * Reads the file contents.
	 *
	 * @param FileReader $reader To read file contents.
	 *
	 * @return Generator
	 */
	public function read( FileReader $reader ): Generator {
		return $reader->read( $this->filepath );
	}

	/**
	 * Returns the size of the file in bytes.
	 *
	 * @return int
	 */
	public function size(): int {
		if ( ! $this->exists() ) {
			return 0;
		}

		return (int) filesize( $this->filepath );
	}

	/**
	 * Stores the message into the specific file.
	 *
	 * @param string             $level   The type of the log message to store.
	 * @param string|\Stringable $message The message to store.
	 * @param array              $context The context.
	 *
	 * @return void
	 */
	public function distribute( string $level, string|\Stringable $message, array $context = [] ): void {
		$this->write( $this->formatter->format( $level, $message, $context ) );
	}

	/**
	 * Truncates the file.
	 *
	 * @return bool
	 */
	public function truncate(): bool {
		if ( ! $this->exists() ) {
			return false;
		}

		$stream = @fopen( $this->filepath, 'w' );

		// Truncate the file to zero bytes.
		if ( $stream ) {
			return ftruncate( $stream, 0 );
		}

		return false;
	}

	/**
	 * Writes to the file per line.
	 *
	 * @param string $formatted The formatted message.
	 *
	 * @return void
	 */
	protected function write( string $formatted ): void {
		file_put_contents( $this->filepath, $formatted, FILE_APPEND );
	}

	/**
	 * Prepares file system.
	 *
	 * @return void
	 */
	protected function prepare_file_system(): void {
        // Creates a directory if it doesn't exist.
        if ( ! is_dir( dirname( $this->filepath ) ) ) {

            $created = wp_mkdir_p( dirname( $this->filepath ) );

            if ( ! $created ) {
                throw new \RuntimeException( sprintf( 'Unable to create a directory: %s', dirname( $this->filepath ) ) );
            }
        }

		$this->touch();
	}

	/**
	 * The file gets created when there's nothing have data to store at the time of file creation.
	 *
	 * @return void
	 */
	protected function touch(): void {
		// Creates an empty file if it doesn't exist.
		if ( ! $this->exists() ) {

			$file = @fopen( $this->filepath, 'wb' );

			if ( $file ) {
				@fclose( $file );
			}
		}
	}
}
