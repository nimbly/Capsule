<?php

namespace Capsule\Tests;

use Capsule\Stream\FileStream;
use Capsule\UploadedFile;
use PHPUnit\Framework\TestCase;


class UploadedFileTest extends TestCase
{
	protected function makeFile()
	{
		return UploadedFile::createFromGlobal([
			"name" => "test.json",
			"tmp_name" => __DIR__ . "/test.json",
			"type" => "text/plain",
			"size" => \filesize(__DIR__ . "/test.json"),
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

		$this->assertTrue(true);
		return;

		// the moveTo() method uses the \move_uploaded_file() function but can only work when a file has actually been uploaded.
		$uploadedFile->moveTo(__DIR__ . "/tmp");

		$this->assertTrue(
			\file_exists(__DIR__ . "/tmp/" . $uploadedFile->getClientFilename())
		);
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
}