<?php

namespace Nimbly\Capsule\Factory;

use Nimbly\Capsule\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

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
		// Parse the URI
		$uriPart = \parse_url($uri);

		if( $uriPart === false ){
			throw new RuntimeException("Malformed URL string.");
		}

		return new Uri(
			!empty($uriPart["scheme"]) ? \strtolower($uriPart["scheme"]) : "http",
			!empty($uriPart["host"]) ? \strtolower($uriPart["host"]) : "",
			$uriPart["path"] ?? "",
			!empty($uriPart["port"]) ? $uriPart["port"] : null,
			$uriPart["user"] ?? "",
			$uriPart["pass"] ?? "",
			$uriPart["query"] ?? "",
			$uriPart["fragment"] ?? ""
		);
	}
}