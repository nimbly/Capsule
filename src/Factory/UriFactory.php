<?php

namespace Capsule\Factory;

use Capsule\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createUri(string $uri = ''): UriInterface
	{
		return self::createFromString($uri);
	}

	/**
	 * Create a Uri instance from a string.
	 *
	 * @param string $uri
	 * @return Uri
	 */
	public static function createFromString(string $uri): UriInterface
	{
		// Parse the URL
		if( ($uriPart = \parse_url($uri)) === false ){
			throw new \Exception("Malformed URL string.");
		}

		return (new Uri)
			->withScheme(!empty($uriPart['scheme']) ? \strtolower($uriPart['scheme']) : "http")
			->withUserInfo($uriPart['user'] ?? "", $uriPart['pass'] ?? "")
			->withHost(!empty($uriPart['host']) ? \strtolower($uriPart['host']) : "")
			->withPort(!empty($uriPart['port']) ? (int) $uriPart['port'] : null)
			->withPath($uriPart['path'] ?? "")
			->withQuery($uriPart['query'] ?? "")
			->withFragment($uriPart['fragment'] ?? "");
	}
}