<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\UriFactory;
use Nimbly\Capsule\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @covers Nimbly\Capsule\Uri
 * @covers Nimbly\Capsule\Factory\UriFactory
 */
class UriTest extends TestCase
{
	public function test_get_port_with_non_provided_returns_null(): void
	{
		$uri = UriFactory::createFromString("http://example.com");
		$this->assertNull($uri->getPort());
	}

	public function test_get_port_with_standard_port_for_http_returns_null(): void
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$this->assertNull($uri->getPort());
	}

	public function test_get_port_with_standard_port_for_https_returns_null(): void
	{
		$uri = UriFactory::createFromString("https://example.com:443");
		$this->assertNull($uri->getPort());
	}

	public function test_get_port_with_non_standard_port_returns_port(): void
	{
		$uri = UriFactory::createFromString("http://example.com:8000");
		$this->assertEquals(8000, $uri->getPort());
	}

	public function test_with_scheme_saves_data()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$uri = $uri->withScheme("https");
		$this->assertEquals("https", $uri->getScheme());
	}

	public function test_with_scheme_is_immutable()
	{
		$uri = UriFactory::createFromString("example.com");
		$newUri = $uri->withScheme("https");

		$this->assertNotEquals($uri, $newUri);
	}

	public function test_with_userinfo_saves_data()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$uri = $uri->withUserInfo("username", "password");
		$this->assertEquals("username:password", $uri->getUserInfo());
	}

	public function test_with_userinfo_is_immutable()
	{
		$uri = UriFactory::createFromString("http://example.com");
		$newUri = $uri->withUserInfo("username", "password");

		$this->assertEmpty($uri->getUserInfo());
		$this->assertNotEquals($uri, $newUri);
	}

	public function test_with_host_saves_data()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$uri = $uri->withHost("www.example.com");
		$this->assertEquals("www.example.com", $uri->getHost());
	}

	public function test_with_host_is_immutable()
	{
		$uri = UriFactory::createFromString("http://example.com");
		$newUri = $uri->withHost("www.example.com");

		$this->assertNotEquals($uri, $newUri);
	}

	public function test_with_port_saves_data()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$uri = $uri->withPort(443);
		$this->assertEquals(443, $uri->getPort());
	}

	public function test_with_port_is_immutable()
	{
		$uri = UriFactory::createFromString("http://example.com");
		$newUri = $uri->withPort(443);

		$this->assertEmpty($uri->getPort());
		$this->assertNotEquals($uri, $newUri);
	}

	public function test_with_path_saves_data()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$uri = $uri->withPath("/some/path/to/resource");
		$this->assertEquals("/some/path/to/resource", $uri->getPath());
	}

	public function test_with_path_is_immutable()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$newUri = $uri->withPath("/some/path/to/resource");

		$this->assertEmpty($uri->getPath());
		$this->assertNotEquals($uri, $newUri);
	}

	public function test_with_query_saves_data()
	{
		$uri = UriFactory::createFromString("http://example.com:80?q=foo&s=some search text");
		$this->assertEquals("q=foo&s=some search text", $uri->getQuery());
	}

	public function test_with_query_is_immutable()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$newUri = $uri->withQuery("q=foo&s=some search text");

		$this->assertEmpty($uri->getQuery());
		$this->assertNotEquals($uri, $newUri);
	}

	public function test_with_fragment_saves_data()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$uri = $uri->withFragment("Chapter1");
		$this->assertEquals("Chapter1", $uri->getFragment());
	}

	public function test_with_fragment_is_immutable()
	{
		$uri = UriFactory::createFromString("http://example.com:80");
		$newUri = $uri->withFragment("Chapter1");

		$this->assertEmpty($uri->getFragment());
		$this->assertNotEquals($uri, $newUri);
	}

	public function test_to_string(): void
	{
		$uri = new Uri("https", "example.com", "foo", 443, "username", "password", "a=value1&b=value2", "anchor1");

		$this->assertEquals(
			"https://username:password@example.com/foo?a=value1&b=value2#anchor1",
			(string) $uri
		);
	}
}