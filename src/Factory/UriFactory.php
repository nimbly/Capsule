<?php declare(strict_types=1);

namespace Capsule\Factory;

use Capsule\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

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
	 * @throws RuntimeException
	 * @return Uri
	 */
	public static function createFromString(string $uri): UriInterface
	{
		// Parse the URI
		$uriPart = \parse_url($uri);

		if( $uriPart === false ){
			throw new RuntimeException("Malformed URL string.");
		}

		return (new Uri)
			->withScheme(!empty($uriPart['scheme']) ? \strtolower($uriPart['scheme']) : "http")
			->withUserInfo($uriPart['user'] ?? "", $uriPart['pass'] ?? "")
			->withHost(!empty($uriPart['host']) ? \strtolower($uriPart['host']) : "")
			->withPort(!empty($uriPart['port']) ? $uriPart['port'] : null)
			->withPath($uriPart['path'] ?? "")
			->withQuery($uriPart['query'] ?? "")
			->withFragment($uriPart['fragment'] ?? "");
	}
}