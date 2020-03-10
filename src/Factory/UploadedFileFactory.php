<?php declare(strict_types=1);

namespace Capsule\Factory;

use Capsule\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
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
	 * @param array $file
	 * @return UploadedFile
	 */
	public static function createFromGlobal(array $file): UploadedFile
	{
		return new UploadedFile(
			$file['tmp_name'] ?? "",
			$file['name'] ?? null,
			$file['type'] ?? null,
			(int) ($file['size'] ?? 0),
			(int) ($file['error'] ?? UPLOAD_ERR_OK)
		);
	}
}