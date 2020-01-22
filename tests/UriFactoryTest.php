<?php

namespace Capsule\Tests;

use Capsule\Factory\UriFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\Factory\UriFactory
 * @covers Capsule\Uri
 */
class UriFactoryTest extends TestCase
{
	public function test_create_uri()
	{
		$url = "https://username:password@www.example.com:443/path/to/some/resource?q=foo&s=some+search+text&n=John%20Doe#fragment-1";
		$uriFactory = new UriFactory;
		$uri = $uriFactory->createUri($url);

		$this->assertEquals("https", $uri->getScheme());
        $this->assertEquals("username:password", $uri->getUserInfo());
        $this->assertEquals("www.example.com", $uri->getHost());
        $this->assertEquals(443, $uri->getPort());
        $this->assertEquals("/path/to/some/resource", $uri->getPath());
        $this->assertEquals("q=foo&s=some+search+text&n=John%20Doe", $uri->getQuery());
        $this->assertEquals("fragment-1", $uri->getFragment());
        $this->assertEquals("username:password@www.example.com:443", $uri->getAuthority());
	}

	public function test_make_from_string_parses_all_uri_parts()
    {
        $url = "https://username:password@www.example.com:443/path/to/some/resource?q=foo&s=some+search+text&n=John%20Doe#fragment-1";
        $uri = UriFactory::createFromString($url);

        $this->assertEquals("https", $uri->getScheme());
        $this->assertEquals("username:password", $uri->getUserInfo());
        $this->assertEquals("www.example.com", $uri->getHost());
        $this->assertEquals(443, $uri->getPort());
        $this->assertEquals("/path/to/some/resource", $uri->getPath());
        $this->assertEquals("q=foo&s=some+search+text&n=John%20Doe", $uri->getQuery());
        $this->assertEquals("fragment-1", $uri->getFragment());
        $this->assertEquals("username:password@www.example.com:443", $uri->getAuthority());
	}

	public function test_make_from_string_throws_exception_on_malformed_url()
	{
		$this->expectException(\Exception::class);
		$uri = UriFactory::createFromString("//::🖕");
	}

    public function test_uri_cast_as_string()
    {
        $url = "https://username:password@www.example.com:443/path/to/some/resource?q=foo&s=some+search+text&n=John%20Doe#fragment-1";
        $uri = UriFactory::createFromString($url);
        $this->assertEquals($url, (string) $uri);
    }
}