<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Stream\BufferStream;
use Nimbly\Capsule\Stream\ResourceStream;
use Nimbly\Capsule\UploadedFile;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * @covers Nimbly\Capsule\UploadedFile
 * @covers Nimbly\Capsule\Stream\ResourceStream
 * @covers Nimbly\Capsule\Stream\BufferStream
 */
class UploadedFileTest extends TestCase
{
	protected function makeFile(): UploadedFile
	{
		if( !\is_dir(__DIR__ . "/tmp") ){
			\mkdir(__DIR__ . "/tmp");
		}

		\file_put_contents(__DIR__ . "/tmp/tmp_upload", "{\"name\": \"Test\", \"email\": \"test@example.com\"}");

		if( \file_exists(__DIR__ . "/tmp/test.json") ){
			\unlink(__DIR__ . "/tmp/test.json");
		}

		return new UploadedFile(
			new ResourceStream(\fopen(__DIR__ . "/tmp/tmp_upload", "r")),
			"test.json",
			"text/plain",
			\filesize(__DIR__ . "/tmp/tmp_upload")
		);
	}

	public function test_get_stream_from_stream(): void
	{
		$stream = new BufferStream("Capsule!");
		$uploadedFile = new UploadedFile($stream);

		$this->assertSame(
			$stream,
			$uploadedFile->getStream()
		);
	}

	public function test_create_from_file_path(): void
	{
		$uploadedFile = new UploadedFile(__DIR__ . "/fixtures/test.json");

		$this->assertInstanceOf(StreamInterface::class, $uploadedFile->getStream());
	}

	public function test_get_stream_from_file(): void
	{
		$uploadedFile = $this->makeFile();

		$this->assertInstanceOf(ResourceStream::class, $uploadedFile->getStream());
	}

	public function test_move_to(): void
	{
		$uploadedFile = $this->makeFile();

		$uploadedFile->moveTo(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename());

		$this->assertTrue(
			\file_exists(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename())
		);
	}

	public function test_move_to_empty_target_path_throws_runtime_exception(): void
	{
		$uploadedFile = $this->makeFile();

		$this->expectException(RuntimeException::class);
		$uploadedFile->moveTo("");
	}

	public function test_move_to_unwriteable_target_throws_runtime_exception(): void
	{
		$uploadedFile = $this->makeFile();

		$this->expectException(RuntimeException::class);
		$uploadedFile->moveTo("/root");
	}

	public function test_calling_move_to_more_than_once_throws_exception(): void
	{
		$uploadedFile = $this->makeFile();
		$uploadedFile->moveTo(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename());

		$this->expectException(RuntimeException::class);
		$uploadedFile->moveTo(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename());
	}

	public function test_get_size(): void
	{
		$uploadedFile = $this->makeFile();

		$this->assertEquals(45, $uploadedFile->getSize());
	}

	public function test_get_error(): void
	{
		$uploadedFile = $this->makeFile();

		$this->assertEquals(
			UPLOAD_ERR_OK,
			$uploadedFile->getError()
		);
	}

	public function test_get_client_filename(): void
	{
		$uploadedFile = $this->makeFile();

		$this->assertEquals(
			"test.json",
			$uploadedFile->getClientFilename()
		);
	}

	public function test_get_client_media_type(): void
	{
		$uploadedFile = $this->makeFile();

		$this->assertEquals(
			"text/plain",
			$uploadedFile->getClientMediaType()
		);
	}

	public function test_getting_stream_after_moving_should_throw_exception(): void
	{
		$uploadedFile = $this->makeFile();
		$uploadedFile->moveTo(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename());

		$this->expectException(RuntimeException::class);
		$uploadedFile->getStream();
	}
}