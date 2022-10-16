<?php

namespace Nimbly\Capsule\Factory;

use Nimbly\Capsule\Stream\BufferStream;
use Nimbly\Capsule\Stream\ResourceStream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StreamFactory implements StreamFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createStream(string $content = ""): StreamInterface
	{
		$fh = \fopen("php://temp", "w+");

		if( $fh === false ){
			throw new RuntimeException("Failed to create stream from php://temp.");
		}

		$stream = new ResourceStream($fh);

		$stream->write($content);
		$stream->rewind();

		return $stream;
	}

	/**
	 * @inheritDoc
	 */
	public function createStreamFromFile(string $filename, string $mode = "r"): StreamInterface
	{
		return self::createFromFile($filename, $mode);
	}

	/**
	 * @inheritDoc
	 */
	public function createStreamFromResource($resource): StreamInterface
	{
		return new ResourceStream($resource);
	}

	/**
	 * Create a BufferStream from a string.
	 *
	 * @param string $contents
	 * @return StreamInterface
	 */
	public static function createFromString(string $contents): StreamInterface
	{
		return new BufferStream($contents);
	}

	/**
	 * Create a StreamInterface instance from a file.
	 *
	 * @param string $filename
	 * @param string $mode
	 * @return StreamInterface
	 */
	public static function createFromFile(string $filename, string $mode = "w+"): StreamInterface
	{
		$fh = \fopen($filename, $mode);

		if( $fh === false ){
			throw new RuntimeException("Failed to open file for reading.");
		}

		return new ResourceStream($fh);
	}

	/**
	 * Create a StreamInterface instance from a resource.
	 *
	 * @param resource $resource
	 * @return StreamInterface
	 */
	public static function createFromResource($resource): StreamInterface
	{
		return new ResourceStream($resource);
	}
}