<?php declare(strict_types=1);

namespace Capsule\Factory;

use Capsule\Stream\ResourceStream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StreamFactory implements StreamFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createStream(string $content = ''): StreamInterface
	{
		$stream = new ResourceStream(
			\fopen("php://temp", "w+")
		);

		$stream->write($content);
		$stream->rewind();

		return $stream;
	}

	/**
	 * @inheritDoc
	 */
	public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
	{
		$fh = \fopen($filename, $mode);

		if( empty($fh) ){
			throw new RuntimeException("");
		}

		return new ResourceStream($fh);
	}

	/**
	 * @inheritDoc
	 */
	public function createStreamFromResource($resource): StreamInterface
	{
		return new ResourceStream($resource);
	}
}