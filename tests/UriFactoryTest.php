<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\UriFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers Nimbly\Capsule\Factory\UriFactory
 * @covers Nimbly\Capsule\Uri
 */
class UriFactoryTest extends TestCase
{
	public function test_create_uri(): void
	{
		$url = "https://username:password@www.example.com:443/path/to/some/resource?q=foo&s=some+search+text&n=John%20Doe#fragment-1";
		$uriFactory = new UriFactory;
		$uri = $uriFactory->createUri($url);

		$this->assertEquals("https", $uri->getScheme());
		$this->assertEquals("username:password", $uri->getUserInfo());
		$this->assertEquals("www.example.com", $uri->getHost());
		$this->assertNull($uri->getPort());
		$this->assertEquals("/path/to/some/resource", $uri->getPath());
		$this->assertEquals("q=foo&s=some+search+text&n=John%20Doe", $uri->getQuery());
		$this->assertEquals("fragment-1", $uri->getFragment());
		$this->assertEquals("username:password@www.example.com", $uri->getAuthority());
	}

	public function test_make_from_string_parses_all_uri_parts(): void
	{
		$url = "https://username:password@www.example.com:443/path/to/some/resource?q=foo&s=some+search+text&n=John%20Doe#fragment-1";
		$uri = UriFactory::createFromString($url);

		$this->assertEquals("https", $uri->getScheme());
		$this->assertEquals("username:password", $uri->getUserInfo());
		$this->assertEquals("www.example.com", $uri->getHost());
		$this->assertNull($uri->getPort());
		$this->assertEquals("/path/to/some/resource", $uri->getPath());
		$this->assertEquals("q=foo&s=some+search+text&n=John%20Doe", $uri->getQuery());
		$this->assertEquals("fragment-1", $uri->getFragment());
		$this->assertEquals("username:password@www.example.com", $uri->getAuthority());
	}

	public function test_make_from_string_throws_exception_on_malformed_url(): void
	{
		$this->expectException(\Exception::class);
		$uri = UriFactory::createFromString("//::🖕");
	}

	public function test_uri_cast_as_string(): void
	{
		$url = "https://username:password@www.example.com:443/path/to/some/resource?q=foo&s=some+search+text&n=John%20Doe#fragment-1";
		$uri = UriFactory::createFromString($url);
		$this->assertEquals(
			"https://username:password@www.example.com/path/to/some/resource?q=foo&s=some+search+text&n=John%20Doe#fragment-1",
			(string) $uri
		);
	}
}