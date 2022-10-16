<?php

namespace Nimbly\Capsule\Factory;

use Nimbly\Capsule\Stream\ResourceStream;
use Nimbly\Capsule\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {

		return new UploadedFile(
			$stream,
			$clientFilename,
			$clientMediaType,
			$size,
			$error
		);
	}

	/**
	 * Create an UploadedFile instance from a single $_FILES element.
	 *
	 * @param array{tmp_name:string,name:string,type:string,size:int,error:int} $file
	 * @throws RuntimeException
	 * @return UploadedFile
	 */
	public static function createFromGlobal(array $file): UploadedFile
	{
		$fh = \fopen($file["tmp_name"], "r");

		if( $fh === false ){
			throw new RuntimeException("Failed to open file for reading.");
		}

		return new UploadedFile(
			new ResourceStream($fh),
			$file["name"] ?? null,
			$file["type"] ?? null,
			$file["size"] ?? 0,
			$file["error"] ?? UPLOAD_ERR_OK
		);
	}
}