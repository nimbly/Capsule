<?php

namespace Nimbly\Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Nimbly\Capsule\Stream\ResourceStream;
use RuntimeException;

/**
 * @covers Nimbly\Capsule\Stream\ResourceStream
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

	public function test_constructor_applies_data(): void
	{
		$resourceStream = $this->getResourceStream();
		$this->assertEquals("Capsule!", $resourceStream->getContents());
	}

	public function test_constructor_rejects_non_resources(): void
	{
		$this->expectException(RuntimeException::class);

		$resourceStream = new ResourceStream("Hello World!");
	}

	public function test_constructor_rejects_non_stream_resources(): void
	{
		$this->expectException(RuntimeException::class);

		$resourceStream = new ResourceStream(
			\curl_init()
		);
	}

	public function test_casting_to_string_returns_contents(): void
	{
		$resourceStream = $this->getResourceStream();
		$this->assertEquals("Capsule!", (string) $resourceStream);
	}

	public function test_casting_to_string_attempts_to_rewind(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->read(2);
		$this->assertEquals("Capsule!", (string) $resourceStream);
	}

	public function test_close_closes_stream(): void
	{
		$resource = \fopen("php://temp", "w+");
		$resourceStream = new ResourceStream($resource);
		$resourceStream->close();

		$this->assertTrue(!\is_resource($resource));
	}

	public function test_detach_returns_stream_resource(): void
	{
		$resource = \fopen("php://temp", "w+");
		$resourceStream = new ResourceStream($resource);

		$this->assertSame(
			$resource,
			$resourceStream->detach()
		);
	}

	public function test_detach_on_null_stream_resource_returns_null(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->close();

		$this->assertNull(
			$resourceStream->detach()
		);
	}

	public function test_get_size_returns_length_of_stream(): void
	{
		$resourceStream = $this->getResourceStream();

		$this->assertEquals(8, $resourceStream->getSize());
	}

	public function test_get_size_returns_null_for_detached_resource(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertNull($resourceStream->getSize());
	}

	public function test_tell_on_resource_returns_position(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->read(2);
		$this->assertEquals(2, $resourceStream->tell());
	}

	public function test_tell_on_detached_resource_throws_runtime_exception(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->tell();
	}

	public function test_eof_when_stream_is_empty(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->getContents();
		$this->assertTrue($resourceStream->eof());
	}

	public function test_eof_returns_true_on_detached_resource(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertTrue($resourceStream->eof());
	}

	public function test_is_seekable(): void
	{
		$resourceStream = $this->getResourceStream();
		$this->assertTrue($resourceStream->isSeekable());
	}

	public function test_is_seekable_on_detached_resource_returns_false(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertFalse($resourceStream->isSeekable());
	}

	public function test_seek(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->seek(2);
		$this->assertEquals("psule!", $resourceStream->read(128));
	}

	public function test_seek_on_detached_resource_throws_runtime_exception(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->seek(0);
	}

	public function test_rewind(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->seek(8);
		$resourceStream->rewind();
		$this->assertEquals("Capsule!", $resourceStream->getContents());
	}

	public function test_rewind_on_detached_resource_throws_runtime_exception(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->rewind();
	}

	public function test_is_writeable(): void
	{
		$resourceStream = $this->getResourceStream();
		$this->assertTrue($resourceStream->isWritable());
	}

	public function test_is_writable_on_detached_resource_returns_false(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertFalse($resourceStream->isWritable());
	}

	public function test_write_returns_bytes_written(): void
	{
		$resourceStream = $this->getResourceStream();
		$bytesWritten = $resourceStream->write("Capsule!");
		$this->assertEquals(8, $bytesWritten);
	}

	public function test_write(): void
	{
		$resourceStream = new ResourceStream(\fopen("php://temp", "w+"));
		$resourceStream->write("I love Capsule!");
		$resourceStream->rewind();

		$this->assertEquals("I love Capsule!", $resourceStream->getContents());
	}

	public function test_write_on_detached_resource_throws_runtime_exception(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->write("Capsule!");
	}

	public function test_is_readable(): void
	{
		$resourceStream = $this->getResourceStream();
		$this->assertTrue($resourceStream->isReadable());
	}

	public function test_is_readable_on_detached_resource_returns_false(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertFalse($resourceStream->isReadable());
	}

	public function test_reading_more_bytes_than_available(): void
	{
		$resourceStream = $this->getResourceStream();
		$data = $resourceStream->read(25);

		$this->assertEquals("Capsule!", $data);
	}

	public function test_reading_fewer_bytes_than_available(): void
	{
		$resourceStream = $this->getResourceStream();
		$data = $resourceStream->read(2);

		$this->assertEquals("Ca", $data);
	}

	public function test_reading_bytes_removes_from_stream(): void
	{
		$resourceStream = $this->getResourceStream();


		$resourceStream->read(2);
		$data = $resourceStream->read(6);

		$this->assertEquals("psule!", $data);
	}

	public function test_read_on_detached_resource_throws_runtime_exception(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->expectException(RuntimeException::class);
		$resourceStream->read(1);
	}

	public function test_get_contents_returns_entire_stream(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->write('Capsule!');
		$resourceStream->rewind();

		$data = $resourceStream->getContents();
		$this->assertEquals("Capsule!", $data);
	}

	public function test_get_contents_on_detached_resource_throws_runtime_exception(): void
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

	public function test_get_metadata_returns_array(): void
	{
		$resourceStream = $this->getResourceStream();
		$this->assertTrue(
			\is_array($resourceStream->getMetadata())
		);
	}

	public function test_get_unknown_metadata_returns_null(): void
	{
		$resourceStream = $this->getResourceStream();

		$this->assertNull(
			$resourceStream->getMetadata("foo")
		);
	}

	public function test_get_metadata_on_detached_resource_returns_empty_array(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertEquals([], $resourceStream->getMetadata());
	}

	public function test_get_metadata_key_on_detached_resource_returns_null(): void
	{
		$resourceStream = $this->getResourceStream();
		$resourceStream->detach();

		$this->assertNull($resourceStream->getMetadata("foo"));
	}
}