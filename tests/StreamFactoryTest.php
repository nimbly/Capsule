<?php

namespace Nimbly\Capsule\Stream;

use Nimbly\Capsule\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers Nimbly\Capsule\Factory\StreamFactory
 * @covers Nimbly\Capsule\Stream\ResourceStream
 */
class StreamFactoryTest extends TestCase
{
	public function test_create_stream(): void
	{
		$streamFactory = new StreamFactory;

		$stream = $streamFactory->createStream("Capsule!");

		$this->assertInstanceOf(ResourceStream::class, $stream);
		$this->assertEquals("Capsule!", $stream->getContents());
	}

	public function test_create_stream_from_file(): void
	{
		if( !\is_dir(__DIR__ . "/tmp") ){
			\mkdir(__DIR__ . "/tmp");
		}

		\file_put_contents(__DIR__ . "/tmp/tmp_file", "test_create_stream_from_file");

		$streamFactory = new StreamFactory;
		$stream = $streamFactory->createStreamFromFile(__DIR__ . "/tmp/tmp_file");

		$this->assertInstanceOf(ResourceStream::class, $stream);
		$this->assertEquals("test_create_stream_from_file", $stream->getContents());
	}

	public function test_create_stream_from_file_that_fails(): void
	{
		$streamFactory = new StreamFactory;

		$this->expectException(RuntimeException::class);
		$streamFactory->createStreamFromFile("foo");
	}

	public function test_create_stream_from_resource(): void
	{
		if( !\is_dir(__DIR__ . "/tmp") ){
			\mkdir(__DIR__ . "/tmp");
		}

		\file_put_contents(__DIR__ . "/tmp/tmp_file", "test_create_stream_from_resource");

		$streamFactory = new StreamFactory;
		$stream = $streamFactory->createStreamFromResource(\fopen(__DIR__ . "/tmp/tmp_file", "r"));

		$this->assertInstanceOf(ResourceStream::class, $stream);
		$this->assertEquals("test_create_stream_from_resource", $stream->getContents());
	}
}