<?php

namespace Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Stream\ResourceStream;

/**
 * @covers Capsule\Stream\ResourceStream
 */
class ResourceStreamTest extends TestCase
{
    protected function getResourceStream(): ResourceStream
    {
        $fh = \fopen("php://temp", "w+");
        \fwrite($fh, "Capsule!");
        \fseek($fh, 0);

        return new ResourceStream($fh);
    }

    public function test_constructor_applies_data()
    {
        $ResourceStream = $this->getResourceStream();
        $this->assertEquals("Capsule!", $ResourceStream->getContents());
    }

    public function test_casting_to_string_returns_contents()
    {
        $ResourceStream = $this->getResourceStream();
        $this->assertEquals("Capsule!", (string) $ResourceStream);
    }

    public function test_close_closes_file()
    {
        $file = \fopen("php://temp", "w+");
        $ResourceStream = new ResourceStream($file);
        $ResourceStream->close();

        $this->assertTrue(!\is_resource($file));
    }

    public function test_detach_returns_file_resource()
    {
        $file = \fopen("php://temp", "w+");
        $ResourceStream = new ResourceStream($file);

        $this->assertSame(
			$file,
			$ResourceStream->detach()
		);
    }

    public function test_getsize_returns_string_length_of_file()
    {
        $ResourceStream = $this->getResourceStream();

        $this->assertEquals(8, $ResourceStream->getSize());
    }

    public function test_tell_of_ResourceStream_returns_position()
    {
        $ResourceStream = $this->getResourceStream();
        $ResourceStream->read(2);
        $this->assertEquals(2, $ResourceStream->tell());
    }

    public function test_eof_when_stream_is_empty()
    {
        $ResourceStream = $this->getResourceStream();
        $ResourceStream->getContents();
        $this->assertTrue($ResourceStream->eof());
    }

    public function test_is_seekable()
    {
        $ResourceStream = $this->getResourceStream();
        $this->assertTrue($ResourceStream->isSeekable());
    }

    public function test_seek()
    {
        $ResourceStream = $this->getResourceStream();
        $ResourceStream->seek(2);
        $this->assertEquals("psule!", $ResourceStream->getContents());
    }

    public function test_rewind()
    {
        $ResourceStream = $this->getResourceStream();
        $ResourceStream->seek(8);
        $ResourceStream->rewind();
        $this->assertEquals("Capsule!", $ResourceStream->getContents());
    }

    public function test_is_writeable()
    {
        $ResourceStream = $this->getResourceStream();
        $this->assertTrue($ResourceStream->isWritable());
    }

    public function test_write_returns_bytes_written()
    {
        $ResourceStream = $this->getResourceStream();
        $bytesWritten = $ResourceStream->write("Capsule!");
        $this->assertEquals(8, $bytesWritten);
    }

    public function test_write()
    {
        $ResourceStream = new ResourceStream(\fopen("php://temp", "w+"));
        $ResourceStream->write("I love Capsule!");
        $ResourceStream->rewind();

        $this->assertEquals("I love Capsule!", $ResourceStream->getContents());
    }

    public function test_is_readable()
    {
        $ResourceStream = $this->getResourceStream();
        $this->assertTrue($ResourceStream->isReadable());
    }

    public function test_reading_more_bytes_than_available()
    {
        $ResourceStream = $this->getResourceStream();
        $data = $ResourceStream->read(25);

        $this->assertEquals("Capsule!", $data);
    }

    public function test_reading_fewer_bytes_than_available()
    {
        $ResourceStream = $this->getResourceStream();
        $data = $ResourceStream->read(2);

        $this->assertEquals("Ca", $data);
    }

    public function test_reading_bytes_removes_from_stream()
    {
        $ResourceStream = $this->getResourceStream();
        $ResourceStream->read(2);
        $data = $ResourceStream->read(6);

        $this->assertEquals("psule!", $data);
    }

    public function test_get_contents_returns_entire_buffer()
    {
        $ResourceStream = $this->getResourceStream();
        $data = $ResourceStream->getContents();
        $this->assertEquals("Capsule!", $data);
    }

    public function test_get_meta_data_returns_array()
    {
        $ResourceStream = $this->getResourceStream();
        $this->assertTrue(
			\is_array($ResourceStream->getMetadata())
		);
    }

    public function test_get_unknown_meta_returns_null()
    {
        $ResourceStream = $this->getResourceStream();

		$this->assertNull(
			$ResourceStream->getMetadata("foo")
		);
    }
}