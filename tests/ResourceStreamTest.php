<?php

namespace Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Stream\ResourceStream;
use RuntimeException;

/**
 * @covers Capsule\Stream\ResourceStream
 */
class ResourceStreamTest extends TestCase
{
	protected function getResourceStream(): ResourceStream
	{
		$fh = \fopen("php://temp", "w+");
		\fwrite($fh, 'Capsule!');
		\fseek($fh, 0);

		return new ResourceStream($fh);
	}

	public function test_constructor_applies_data()
	{
		$resourceStream = $this->getResourceStream();
		$this->assertEquals("Capsule!", $resourceStream->getContents());
	}

	public function test_constructor_rejects_non_resources()
	{
		$this->expectException(RuntimeException::class);

		$resourceStream = new ResourceStream("Hello World!");
	}

	public function test_constructor_rejects_non_stream_resources()
	{
		$this->expectException(RuntimeException::class);

		$resourceStream = new ResourceStream(
			\curl_init()
		);
	}

	public function test_casting_to_string_returns_contents()
	{
		$resourceStream = $this->getResourceStream();
		$this->assertEquals("Capsule!", (string) $resourceStream);
	}

	public function test_casting_to_string_attempts_to_rewind()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->read(2);
		$this->assertEquals("Capsule!", (string) $resourceStream);
	}

	public function test_close_closes_stream()
	{
		$resource = \fopen("php://temp", "w+");
		$resourceStream = new ResourceStream($resource);
		$resourceStream->close();

		$this->assertTrue(!\is_resource($resource));
	}

	public function test_detach_returns_stream_resource()
	{
		$resource = \fopen("php://temp", "w+");
		$resourceStream = new ResourceStream($resource);

		$this->assertSame(
			$resource,
			$resourceStream->detach()
		);
	}

	public function test_detach_on_null_stream_resource_returns_null()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->close();

		$this->assertNull(
			$resourceStream->detach()
		);
	}

	public function test_get_size_returns_length_of_stream()
	{
		$resourceStream = $this->getResourceStream();

		$this->assertEquals(8, $resourceStream->getSize());
	}

	public function test_get_size_returns_null_for_detached_resource()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertNull($resourceStream->getSize());
	}

	public function test_tell_on_resource_returns_position()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->read(2);
		$this->assertEquals(2, $resourceStream->tell());
	}

	public function test_tell_on_detached_resource_throws_runtime_exception()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->tell();
	}

	public function test_eof_when_stream_is_empty()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->getContents();
		$this->assertTrue($resourceStream->eof());
	}

	public function test_eof_returns_true_on_detached_resource()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertTrue($resourceStream->eof());
	}

	public function test_is_seekable()
	{
		$resourceStream = $this->getResourceStream();
		$this->assertTrue($resourceStream->isSeekable());
	}

	public function test_is_seekable_on_detached_resource_returns_false()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertFalse($resourceStream->isSeekable());
	}

	public function test_seek()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->seek(2);
		$this->assertEquals("psule!", $resourceStream->read(128));
	}

	public function test_seek_on_detached_resource_throws_runtime_exception()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->seek(0);
	}

	public function test_rewind()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->seek(8);
		$resourceStream->rewind();
		$this->assertEquals("Capsule!", $resourceStream->getContents());
	}

	public function test_rewind_on_detached_resource_throws_runtime_exception()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->rewind();
	}

	public function test_is_writeable()
	{
		$resourceStream = $this->getResourceStream();
		$this->assertTrue($resourceStream->isWritable());
	}

	public function test_is_writable_on_detached_resource_returns_false()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertFalse($resourceStream->isWritable());
	}

	public function test_write_returns_bytes_written()
	{
		$resourceStream = $this->getResourceStream();
		$bytesWritten = $resourceStream->write("Capsule!");
		$this->assertEquals(8, $bytesWritten);
	}

	public function test_write()
	{
		$resourceStream = new ResourceStream(\fopen("php://temp", "w+"));
		$resourceStream->write("I love Capsule!");
		$resourceStream->rewind();

		$this->assertEquals("I love Capsule!", $resourceStream->getContents());
	}

	public function test_write_on_detached_resource_throws_runtime_exception()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->write("Capsule!");
	}

	public function test_is_readable()
	{
		$resourceStream = $this->getResourceStream();
		$this->assertTrue($resourceStream->isReadable());
	}

	public function test_is_readable_on_detached_resource_returns_false()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertFalse($resourceStream->isReadable());
	}

	public function test_reading_more_bytes_than_available()
	{
		$resourceStream = $this->getResourceStream();
		$data = $resourceStream->read(25);

		$this->assertEquals("Capsule!", $data);
	}

	public function test_reading_fewer_bytes_than_available()
	{
		$resourceStream = $this->getResourceStream();
		$data = $resourceStream->read(2);

		$this->assertEquals("Ca", $data);
	}

	public function test_reading_bytes_removes_from_stream()
	{
		$resourceStream = $this->getResourceStream();


		$resourceStream->read(2);
		$data = $resourceStream->read(6);

		$this->assertEquals("psule!", $data);
	}

	public function test_read_on_detached_resource_throws_runtime_exception()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->read(1);
	}

	public function test_get_contents_returns_entire_stream()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->write('Capsule!');
		$resourceStream->rewind();

		$data = $resourceStream->getContents();
		$this->assertEquals("Capsule!", $data);
	}

	public function test_get_contents_on_detached_resource_throws_runtime_exception()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->getContents();
	}

	public function test_get_contents_attempts_to_rewind_stream(): void
	{
		$resourceStream = $this->getResourceStream();

		$resourceStream->read(5);

		$this->assertEquals(
			"Capsule!",
			$resourceStream->getContents()
		);
	}

	public function test_get_metadata_returns_array()
	{
		$resourceStream = $this->getResourceStream();
		$this->assertTrue(
			\is_array($resourceStream->getMetadata())
		);
	}

	public function test_get_unknown_metadata_returns_null()
	{
		$resourceStream = $this->getResourceStream();

		$this->assertNull(
			$resourceStream->getMetadata("foo")
		);
	}

	public function test_get_metadata_on_detached_resource_returns_empty_array()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertEquals([], $resourceStream->getMetadata());
	}

	public function test_get_metadata_key_on_detached_resource_returns_null()
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertNull($resourceStream->getMetadata("foo"));
	}
}