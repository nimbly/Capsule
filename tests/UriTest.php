<?php

namespace Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Uri;

/**
 * @covers Capsule\Uri
 */
class UriTest extends TestCase
{
    public function test_with_scheme_saves_data()
    {
        $uri = (new Uri)->withScheme("https");
        $this->assertEquals("https", $uri->getScheme());
    }

    public function test_with_scheme_is_immutable()
    {
        $uri = new Uri;
        $newUri = $uri->withScheme("https");

        $this->assertEmpty($uri->getScheme());
        $this->assertNotEquals($uri, $newUri);
    }

    public function test_with_userinfo_saves_data()
    {
        $uri = (new Uri)->withUserInfo("username", "password");
        $this->assertEquals("username:password", $uri->getUserInfo());
    }

    public function test_with_userinfo_is_immutable()
    {
        $uri = new Uri;
        $newUri = $uri->withUserInfo("username", "password");

        $this->assertEmpty($uri->getScheme());
        $this->assertNotEquals($uri, $newUri);
    }

    public function test_with_host_saves_data()
    {
        $uri = (new Uri)->withHost("www.example.com");
        $this->assertEquals("www.example.com", $uri->getHost());
    }

    public function test_with_host_is_immutable()
    {
        $uri = new Uri;
        $newUri = $uri->withHost("www.example.com");

        $this->assertEmpty($uri->getHost());
        $this->assertNotEquals($uri, $newUri);
    }

    public function test_with_port_saves_data()
    {
        $uri = (new Uri)->withPort(443);
        $this->assertEquals(443, $uri->getPort());
    }

    public function test_with_port_is_immutable()
    {
        $uri = new Uri;
        $newUri = $uri->withPort(443);

        $this->assertEmpty($uri->getPort());
        $this->assertNotEquals($uri, $newUri);
    }

    public function test_with_path_saves_data()
    {
        $uri = (new Uri)->withPath("/some/path/to/resource");
        $this->assertEquals("/some/path/to/resource", $uri->getPath());
    }

    public function test_with_path_is_immutable()
    {
        $uri = new Uri;
        $newUri = $uri->withPath("/some/path/to/resource");

        $this->assertEmpty($uri->getPath());
        $this->assertNotEquals($uri, $newUri);
    }

    public function test_with_query_saves_data()
    {
        $uri = (new Uri)->withQuery("q=foo&s=some search text");
        $this->assertEquals("q=foo&s=some search text", $uri->getQuery());
    }

    public function test_with_query_is_immutable()
    {
        $uri = new Uri;
        $newUri = $uri->withQuery("q=foo&s=some search text");

        $this->assertEmpty($uri->getQuery());
        $this->assertNotEquals($uri, $newUri);
    }

    public function test_with_fragment_saves_data()
    {
        $uri = (new Uri)->withFragment("Chapter1");
        $this->assertEquals("Chapter1", $uri->getFragment());
    }

    public function test_with_fragment_is_immutable()
    {
        $uri = new Uri;
        $newUri = $uri->withFragment("Chapter1");

        $this->assertEmpty($uri->getFragment());
        $this->assertNotEquals($uri, $newUri);
    }

    public function test_get_authority_with_no_credentials_returns_empty_string()
    {
		$uri = new Uri;
        $this->assertEquals("", $uri->getAuthority());
    }
}