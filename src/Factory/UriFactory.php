<?php

namespace Nimbly\Capsule\Factory;

use Nimbly\Capsule\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

/**
 * With this factory you can generate a Uri instance.
 */
class UriFactory implements UriFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createUri(string $uri = ""): UriInterface
	{
		return self::createFromString($uri);
	}

	/**
	 * Create a Uri instance from a string.
	 *
	 * @param string $uri
	 * @throws RuntimeException
	 * @return Uri
	 */
	public static function createFromString(string $uri): Uri
	{
		$uriPart = \parse_url($uri);

		if( $uriPart === false ){
			throw new RuntimeException("Malformed URL string.");
		}

		return new Uri(
			!empty($uriPart["scheme"]) ? \strtolower($uriPart["scheme"]) : null,
			!empty($uriPart["host"]) ? \strtolower($uriPart["host"]) : null,
			$uriPart["path"] ?? null,
			!empty($uriPart["port"]) ? $uriPart["port"] : null,
			$uriPart["user"] ?? null,
			$uriPart["pass"] ?? null,
			$uriPart["query"] ?? null,
			$uriPart["fragment"] ?? null
		);
	}
}