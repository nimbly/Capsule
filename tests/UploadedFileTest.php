<?php

namespace Capsule\Tests;

use Capsule\Stream\FileStream;
use Capsule\UploadedFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers Capsule\UploadedFile
 * @covers Capsule\Stream\FileStream
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

		return UploadedFile::createFromGlobal([
			"name" => "test.json",
			"tmp_name" => __DIR__ . "/tmp/tmp_upload",
			"type" => "text/plain",
			"size" => \filesize(__DIR__ . "/tmp/tmp_upload"),
			"error" => UPLOAD_ERR_OK
		]);
	}

	public function test_get_stream()
	{
		$uploadedFile = $this->makeFile();

		$this->assertTrue($uploadedFile->getStream() instanceof FileStream);
	}

	public function test_move_to()
	{
		$uploadedFile = $this->makeFile();

		$uploadedFile->moveTo(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename());

		$this->assertTrue(
			\file_exists(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename())
		);
	}

	public function test_calling_move_to_more_than_once_throws_exception()
	{
		$uploadedFile = $this->makeFile();
		$uploadedFile->moveTo(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename());

		$this->expectException(RuntimeException::class);
		$uploadedFile->moveTo(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename());
	}

	public function test_get_size()
	{
		$uploadedFile = $this->makeFile();

		$this->assertEquals(45, $uploadedFile->getSize());
	}

	public function test_get_error()
	{
		$uploadedFile = $this->makeFile();

		$this->assertEquals(
			UPLOAD_ERR_OK,
			$uploadedFile->getError()
		);
	}

	public function test_get_client_filename()
	{
		$uploadedFile = $this->makeFile();

		$this->assertEquals(
			"test.json",
			$uploadedFile->getClientFilename()
		);
	}

	public function test_get_client_media_type()
	{
		$uploadedFile = $this->makeFile();

		$this->assertEquals(
			"text/plain",
			$uploadedFile->getClientMediaType()
		);
	}

	public function test_getting_stream_after_moving_should_throw_exception()
	{
		$uploadedFile = $this->makeFile();
		$uploadedFile->moveTo(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename());

		$this->expectException(RuntimeException::class);
		$uploadedFile->getStream();
	}
}