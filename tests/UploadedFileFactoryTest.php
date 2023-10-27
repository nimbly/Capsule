<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\UploadedFileFactory;
use Nimbly\Capsule\Stream\BufferStream;
use Nimbly\Capsule\UploadedFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers Nimbly\Capsule\Factory\StreamFactory
 * @covers Nimbly\Capsule\Factory\UploadedFileFactory
 * @covers Nimbly\Capsule\UploadedFile
 * @covers Nimbly\Capsule\Stream\BufferStream
 * @covers Nimbly\Capsule\Stream\ResourceStream
 */
class UploadedFileFactoryTest extends TestCase
{
	public function test_create_uploaded_file(): void
	{
		$stream = new BufferStream("Capsule!");

		$uploadedFileFactory = new UploadedFileFactory;
		$uploadedFile = $uploadedFileFactory->createUploadedFile(
			$stream,
			$stream->getSize(),
			UPLOAD_ERR_OK,
			"test.json",
			"text/json"
		);

		$this->assertInstanceOf(UploadedFile::class, $uploadedFile);
		$this->assertEquals("test.json", $uploadedFile->getClientFilename());
		$this->assertEquals("text/json", $uploadedFile->getClientMediaType());
		$this->assertEquals($stream->getSize(), $uploadedFile->getSize());
		$this->assertEquals(UPLOAD_ERR_OK, $uploadedFile->getError());
	}

	public function test_create_from_global(): void
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

	public function test_create_from_global_file_open_error_throws_runtime_exception(): void
	{
		$this->expectException(RuntimeException::class);

		$uploadedFile = UploadedFileFactory::createFromGlobal([
			"tmp_name" => "foo",
			"name" => "test.json",
			"type" => "text/json",
			"size" => \filesize(__DIR__ . "/tmp/tmp_upload"),
			"error" => UPLOAD_ERR_OK
		]);
	}

	public function test_create_from_globals_with_single_file(): void
	{
		if( !\is_dir(__DIR__ . "/tmp") ){
			\mkdir(__DIR__ . "/tmp");
		}

		\file_put_contents(__DIR__ . "/tmp/tmp_upload", "{\"name\": \"Test\", \"email\": \"test@example.com\"}");

		$files = [
			[
				"tmp_name" => __DIR__ . "/tmp/tmp_upload",
				"name" => "test.json",
				"type" => "text/json",
				"size" => \filesize(__DIR__ . "/tmp/tmp_upload"),
				"error" => UPLOAD_ERR_OK
			]
		];

		$uploadedFiles = UploadedFileFactory::createFromGlobals($files);

		$this->assertCount(1, $uploadedFiles);

		$this->assertInstanceOf(UploadedFile::class, $uploadedFiles[0]);
		$this->assertEquals("test.json", $uploadedFiles[0]->getClientFilename());
		$this->assertEquals("text/json", $uploadedFiles[0]->getClientMediaType());
		$this->assertEquals(\filesize(__DIR__ . "/tmp/tmp_upload"), $uploadedFiles[0]->getSize());
		$this->assertEquals(UPLOAD_ERR_OK, $uploadedFiles[0]->getError());
	}

	public function test_create_from_globals_with_multiple_files(): void
	{
		if( !\is_dir(__DIR__ . "/tmp") ){
			\mkdir(__DIR__ . "/tmp");
		}

		\file_put_contents(__DIR__ . "/tmp/tmp_upload", "{\"name\": \"Test\", \"email\": \"test@example.com\"}");
		\file_put_contents(__DIR__ . "/tmp/tmp_upload2", "{\"name\": \"Test2\", \"email\": \"test2@example.com\"}");

		$files = [
			[
				"tmp_name" => __DIR__ . "/tmp/tmp_upload",
				"name" => "test.json",
				"type" => "text/json",
				"size" => \filesize(__DIR__ . "/tmp/tmp_upload"),
				"error" => UPLOAD_ERR_OK
			],

			[
				"tmp_name" => __DIR__ . "/tmp/tmp_upload2",
				"name" => "test.json",
				"type" => "text/json",
				"size" => \filesize(__DIR__ . "/tmp/tmp_upload2"),
				"error" => UPLOAD_ERR_OK
			],
		];

		$uploadedFiles = UploadedFileFactory::createFromGlobals($files);

		$this->assertCount(2, $uploadedFiles);

		$this->assertInstanceOf(UploadedFile::class, $uploadedFiles[0]);
		$this->assertEquals("test.json", $uploadedFiles[0]->getClientFilename());
		$this->assertEquals("text/json", $uploadedFiles[0]->getClientMediaType());
		$this->assertEquals(\filesize(__DIR__ . "/tmp/tmp_upload"), $uploadedFiles[0]->getSize());
		$this->assertEquals(UPLOAD_ERR_OK, $uploadedFiles[0]->getError());

		$this->assertInstanceOf(UploadedFile::class, $uploadedFiles[1]);
		$this->assertEquals("test.json", $uploadedFiles[1]->getClientFilename());
		$this->assertEquals("text/json", $uploadedFiles[1]->getClientMediaType());
		$this->assertEquals(\filesize(__DIR__ . "/tmp/tmp_upload2"), $uploadedFiles[1]->getSize());
		$this->assertEquals(UPLOAD_ERR_OK, $uploadedFiles[1]->getError());
	}

	public function test_create_from_globals_with_multiple_nested_files(): void
	{
		if( !\is_dir(__DIR__ . "/tmp") ){
			\mkdir(__DIR__ . "/tmp");
		}

		\file_put_contents(__DIR__ . "/tmp/tmp_upload", "{\"name\": \"Test\", \"email\": \"test@example.com\"}");
		\file_put_contents(__DIR__ . "/tmp/tmp_upload2", "{\"name\": \"Test2\", \"email\": \"test2@example.com\"}");

		$files = [
			"upload" => [
				"tmp_name" => [
					__DIR__ . "/tmp/tmp_upload",
					__DIR__ . "/tmp/tmp_upload2"
				],

				"name" => [
					"test.json",
					"test2.json"
				],

				"type" => [
					"text/json",
					"text/json"
				],

				"size" => [
					\filesize(__DIR__ . "/tmp/tmp_upload"),
					\filesize(__DIR__ . "/tmp/tmp_upload2")
				],

				"error" => [
					UPLOAD_ERR_OK,
					UPLOAD_ERR_OK
				]
			]
		];

		$uploadedFiles = UploadedFileFactory::createFromGlobals($files);

		$this->assertCount(2, $uploadedFiles);

		$this->assertInstanceOf(UploadedFile::class, $uploadedFiles[0]);
		$this->assertEquals("test.json", $uploadedFiles[0]->getClientFilename());
		$this->assertEquals("text/json", $uploadedFiles[0]->getClientMediaType());
		$this->assertEquals(\filesize(__DIR__ . "/tmp/tmp_upload"), $uploadedFiles[0]->getSize());
		$this->assertEquals(UPLOAD_ERR_OK, $uploadedFiles[0]->getError());

		$this->assertInstanceOf(UploadedFile::class, $uploadedFiles[1]);
		$this->assertEquals("test.json", $uploadedFiles[1]->getClientFilename());
		$this->assertEquals("text/json", $uploadedFiles[1]->getClientMediaType());
		$this->assertEquals(\filesize(__DIR__ . "/tmp/tmp_upload2"), $uploadedFiles[1]->getSize());
		$this->assertEquals(UPLOAD_ERR_OK, $uploadedFiles[1]->getError());
	}
}