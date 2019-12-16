<?php

namespace Capsule\Tests;

use Capsule\Factory\UploadedFileFactory;
use Capsule\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\Factory\UploadedFileFactory
 * @covers Capsule\UploadedFile
 */
class UploadedFileFactoryTest extends TestCase
{
	public function test_create_from_global()
	{
		if( !\is_dir(__DIR__ . "/tmp") ){
			\mkdir(__DIR__ . "/tmp");
		}

		\file_put_contents(__DIR__ . "/tmp/tmp_upload", "{\"name\": \"Test\", \"email\": \"test@example.com\"}");

		$uploadedFile = UploadedFileFactory::createFromGlobal([
			"tmp_name" => __DIR__ . "/tmp/tmp_upload",
			"name" => "test.json",
			"type" => "text/json",
			"size" => \filesize(__DIR__ . "/tmp/tmp_upload"),
			"error" => UPLOAD_ERR_OK
		]);

		$this->assertInstanceOf(UploadedFile::class, $uploadedFile);
		$this->assertEquals("test.json", $uploadedFile->getClientFilename());
		$this->assertEquals("text/json", $uploadedFile->getClientMediaType());
		$this->assertEquals(\filesize(__DIR__ . "/tmp/tmp_upload"), $uploadedFile->getSize());
		$this->assertEquals(UPLOAD_ERR_OK, $uploadedFile->getError());
	}
}