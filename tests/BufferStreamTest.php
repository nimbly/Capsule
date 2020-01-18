<?php

namespace Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Stream\BufferStream;
use ReflectionClass;
use RuntimeException;
use SebastianBergmann\Environment\Runtime;

/**
 * @covers Capsule\Stream\BufferStream
 */
class BufferStreamTest extends TestCase
{
    public function test_contructor_sets_buffer_contents()
    {
        $bufferStream = new BufferStream("Capsule!");
        $this->assertEquals("Capsule!", $bufferStream->getContents());
    }

    public function test_casting_to_string_returns_contents()
    {
        $bufferStream = new BufferStream("Capsule!");
        $this->assertEquals("Capsule!", (string) $bufferStream);
    }

    public function test_close_resets_buffer_contents()
    {
        $bufferStream = new BufferStream("Capsule!");
        $bufferStream->close();
        $this->assertEquals("", $bufferStream->getContents());
    }

    public function test_detach_returns_null()
    {
        $bufferStream = new BufferStream("Capsule!");
		$this->assertNull($bufferStream->detach());
	}

	public function test_detach_sets_buffer_to_null()
    {
        $bufferStream = new BufferStream("Capsule!");
		$bufferStream->detach();

		$reflectionClass = new ReflectionClass($bufferStream);
		$reflectionProperty = $reflectionClass->getProperty('buffer');
		$reflectionProperty->setAccessible(true);

		$this->assertNull($reflectionProperty->getValue($bufferStream));
    }

    public function test_getsize_returns_string_length_of_buffer()
    {
        $bufferStream = new BufferStream("Capsule!");
        $this->assertEquals(8, $bufferStream->getSize());
	}

	public function test_get_size_on_detached_buffer_returns_null()
    {
		$bufferStream = new BufferStream("Capsule!");
		$bufferStream->detach();

        $this->assertNull($bufferStream->getSize());
    }

    public function test_tell_returns_zero()
    {
        $bufferStream = new BufferStream("Capsule!");
        $this->assertEquals(0, $bufferStream->tell());
	}

	public function test_tell_on_detached_buffer_throws_runtime_exception()
    {
		$bufferStream = new BufferStream("Capsule!");
		$bufferStream->detach();

		$this->expectException(RuntimeException::class);
        $bufferStream->tell();
    }

    public function test_eof_when_buffer_is_empty()
    {
        $bufferStream = new BufferStream;
        $this->assertTrue($bufferStream->eof());
    }

    public function test_is_not_seekable()
    {
        $bufferStream = new BufferStream;
        $this->assertFalse($bufferStream->isSeekable());
    }

    public function test_seek_throws_runtime_exception()
    {
		$bufferStream = new BufferStream("Capsule!");

		$this->expectException(RuntimeException::class);
        $bufferStream->seek(0);
    }

    public function test_rewind_throws_runtime_exception()
    {
        $bufferStream = new BufferStream("Capsule!");
        $this->expectException(RuntimeException::class);
        $bufferStream->rewind();
    }

    public function test_is_writeable_returns_true()
    {
        $bufferStream = new BufferStream;
        $this->assertTrue($bufferStream->isWritable());
	}

	public function test_is_writeable_on_detached_buffer_returns_false()
	{
		$bufferStream = new BufferStream;
		$bufferStream->detach();

		$this->assertFalse($bufferStream->isWritable());
	}

    public function test_write_returns_bytes_written()
    {
        $bufferStream = new BufferStream;
        $bytesWritten = $bufferStream->write("Capsule!");

        $this->assertEquals(8, $bytesWritten);
    }

    public function test_write_appends_data()
    {
        $bufferStream = new BufferStream("I love");
        $bufferStream->write(" Capsule!");

        $this->assertEquals("I love Capsule!", $bufferStream->getContents());
	}

	public function test_write_on_detached_buffer_throws_runtime_exception()
	{
		$bufferStream = new BufferStream;
		$bufferStream->detach();

		$this->expectException(RuntimeException::class);
		$bufferStream->write("Capsule!");
	}

    public function test_is_readable_returns_true()
    {
        $bufferStream = new BufferStream;

        $this->assertTrue($bufferStream->isReadable());
	}

	public function test_is_readable_on_detached_buffer_returns_false()
    {
        $bufferStream = new BufferStream;
		$bufferStream->detach();

        $this->assertFalse($bufferStream->isReadable());
	}

	public function test_read_on_detached_buffer_throws_runtime_exception()
	{
		$bufferStream = new BufferStream;
		$bufferStream->detach();

		$this->expectException(RuntimeException::class);
		$bufferStream->read(1);
	}

    public function test_reading_more_bytes_than_available_returns_full_contents()
    {
        $bufferStream = new BufferStream("Capsule!");
        $data = $bufferStream->read(25);

        $this->assertEquals("Capsule!", $data);
    }

    public function test_reading_fewer_bytes_than_available()
    {
        $bufferStream = new BufferStream("Capsule!");
        $data = $bufferStream->read(2);

        $this->assertEquals("Ca", $data);
    }

    public function test_reading_bytes_removes_from_stream()
    {
        $bufferStream = new BufferStream("Capsule!");
        $bufferStream->read(2);
        $data = $bufferStream->getContents();

        $this->assertEquals("psule!", $data);
    }

    public function test_get_contents_returns_entire_buffer()
    {
        $bufferStream = new BufferStream("Capsule!");
        $data = $bufferStream->getContents();
        $this->assertEquals("Capsule!", $data);
    }

    public function test_get_contents_empties_buffer()
    {
        $bufferStream = new BufferStream("Capsule!");
        $bufferStream->getContents();

        $this->assertEquals("", $bufferStream->getContents());
        $this->assertTrue($bufferStream->eof());
	}

	public function test_get_contents_on_detached_buffer_throws_runtime_exception()
	{
		$bufferStream = new BufferStream;
		$bufferStream->detach();

		$this->expectException(RuntimeException::class);
		$bufferStream->getContents();
	}

    public function test_get_meta_data_returns_nothing()
    {
        $bufferStream = new BufferStream("Capsule!");
        $this->assertEquals([], $bufferStream->getMetadata());
	}

	public function test_get_metadata_on_detached_buffer_returns_empty_array()
	{
		$bufferStream = new BufferStream;
		$bufferStream->detach();

		$this->assertEquals([], $bufferStream->getMetadata());
	}

	public function test_get_metadata_key_on_detached_buffer_returns_null()
	{
		$bufferStream = new BufferStream;
		$bufferStream->detach();

		$this->assertNull($bufferStream->getMetadata("foo"));
	}
}