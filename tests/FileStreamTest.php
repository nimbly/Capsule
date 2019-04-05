<?php

namespace Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Stream\FileStream;

/**
 * @covers Capsule\Stream\FileStream
 */
class FileStreamTest extends TestCase
{
    protected function getFileStream(): FileStream
    {
        $fh = fopen("php://temp", "w+");
        fwrite($fh, "Capsule!");
        fseek($fh, 0);

        return new FileStream($fh);
    }

    public function test_constructor_applies_data()
    {
        $fileStream = $this->getFileStream();
        $this->assertEquals("Capsule!", $fileStream->getContents());
    }

    public function test_casting_to_string_returns_contents()
    {
        $fileStream = $this->getFileStream();
        $this->assertEquals("Capsule!", (string) $fileStream);
    }

    public function test_close_closes_file()
    {
        $file = fopen("php://temp", "w+");
        $fileStream = new FileStream($file);
        $fileStream->close();

        $this->assertTrue(!is_resource($file));
    }

    public function test_detach_closes_file()
    {
        $file = fopen("php://temp", "w+");
        $fileStream = new FileStream($file);
        $fileStream->detach();

        $this->assertTrue(!is_resource($file));
    }

    public function test_getsize_returns_string_length_of_file()
    {
        $fileStream = $this->getFileStream();

        $this->assertEquals(8, $fileStream->getSize());
    }

    public function test_tell_of_filestream_returns_position()
    {
        $fileStream = $this->getFileStream();
        $fileStream->read(2);
        $this->assertEquals(2, $fileStream->tell());
    }

    public function test_eof_when_stream_is_empty()
    {
        $fileStream = $this->getFileStream();
        $fileStream->getContents();
        $this->assertTrue($fileStream->eof());
    }

    public function test_is_seekable()
    {
        $fileStream = $this->getFileStream();
        $this->assertTrue($fileStream->isSeekable());
    }

    public function test_seek()
    {
        $fileStream = $this->getFileStream();
        $fileStream->seek(2);
        $this->assertEquals("psule!", $fileStream->getContents());
    }

    public function test_rewind()
    {
        $fileStream = $this->getFileStream();
        $fileStream->seek(8);
        $fileStream->rewind();
        $this->assertEquals("Capsule!", $fileStream->getContents());
    }

    public function test_is_writeable()
    {
        $fileStream = $this->getFileStream();
        $this->assertTrue($fileStream->isWritable());
    }

    public function test_write_returns_bytes_written()
    {
        $fileStream = $this->getFileStream();
        $bytesWritten = $fileStream->write("Capsule!");
        $this->assertEquals(8, $bytesWritten);
    }

    public function test_write()
    {
        $fileStream = new FileStream(fopen("php://temp", "w+"));
        $fileStream->write("I love Capsule!");
        $fileStream->rewind();

        $this->assertEquals("I love Capsule!", $fileStream->getContents());
    }

    public function test_is_readable()
    {
        $fileStream = $this->getFileStream();
        $this->assertTrue($fileStream->isReadable());
    }

    public function test_reading_more_bytes_than_available()
    {
        $fileStream = $this->getFileStream();
        $data = $fileStream->read(25);

        $this->assertEquals("Capsule!", $data);
    }

    public function test_reading_fewer_bytes_than_available()
    {
        $fileStream = $this->getFileStream();
        $data = $fileStream->read(2);

        $this->assertEquals("Ca", $data);
    }

    public function test_reading_bytes_removes_from_stream()
    {
        $fileStream = $this->getFileStream();
        $fileStream->read(2);
        $data = $fileStream->read(6);

        $this->assertEquals("psule!", $data);
    }

    public function test_get_contents_returns_entire_buffer()
    {
        $fileStream = $this->getFileStream();
        $data = $fileStream->getContents();
        $this->assertEquals("Capsule!", $data);
    }

    public function test_get_meta_data_returns_array()
    {
        $fileStream = $this->getFileStream();
        $this->assertTrue(is_array($fileStream->getMetadata()));
    }
}