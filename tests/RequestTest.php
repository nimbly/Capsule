<?php

namespace Capsule\Tests;

use Capsule\Factory\UriFactory;
use Capsule\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers Capsule\Request
 * @covers Capsule\Factory\UriFactory
 * @covers Capsule\Uri
 * @covers Capsule\Stream\BufferStream
 * @covers Capsule\Stream\ResourceStream
 * @covers Capsule\MessageAbstract
 */
class RequestTest extends TestCase
{
    public function test_with_method_saves_data()
    {
        $request = new Request('post', '/foo');
        $this->assertEquals("POST", $request->getMethod());
    }

    public function test_with_method_is_immutable()
    {
        $request = new Request('get', '/foo');
        $newRequest = $request->withMethod("post");

		$this->assertNotEquals($request->getMethod(), $newRequest->getMethod());
		$this->assertNotSame($request, $newRequest);
    }

    public function test_with_uri_saves_data()
    {
        $uri = UriFactory::createFromString("https://www.example.com");
        $request = new Request('get', $uri);
        $this->assertSame($uri, $request->getUri());
    }

    public function test_with_uri_is_immutable()
    {
        $request = new Request('get', '/foo');
        $newRequest = $request->withUri(UriFactory::createFromString("https://example.com"));

		$this->assertNotSame($request->getUri(), $newRequest->getUri());
        $this->assertNotSame($request, $newRequest);
    }

    public function test_with_request_target_saves_data()
    {
		$request = new Request('get', 'https://example.com:443');
		$request = $request->withRequestTarget("GET example.com:443 HTTP/1.1");

        $this->assertEquals("GET example.com:443 HTTP/1.1", $request->getRequestTarget());
	}

    public function test_with_request_target_is_immutable()
    {
        $request = new Request('get', 'https://example.com:443');
        $newRequest = $request->withRequestTarget("GET example.com:443 HTTP/1.1");

        $this->assertNotSame($request, $newRequest);
	}

	public function test_building_request_target_if_none_provided()
	{
		$request = new Request("get", "http://example.com/sample?q=search");

		$this->assertEquals(
			"/sample?q=search",
			$request->getRequestTarget()
		);
	}

	public function test_building_request_target_if_no_path()
	{
		$request = new Request("get", "http://example.com");

		$this->assertEquals(
			"/",
			$request->getRequestTarget()
		);
	}

    public function test_request_constructor()
    {
        $request = new Request(
            "post",
            "http://example.com",
            "BODY",
            [
                "Accept-Language" => "en_US"
            ],
            "2"
        );

        $this->assertEquals("POST", $request->getMethod());
        $this->assertEquals("http://example.com", (string) $request->getUri());
        $this->assertEquals("BODY", $request->getBody()->getContents());
        $this->assertEquals("en_US", $request->getHeader("Accept-Language")[0]);
        $this->assertEquals("2", $request->getProtocolVersion());
	}

    public function test_body_instance_created_automatically_if_not_provided()
    {
        $request = new Request("get", "http://example.com:80/");

        $this->assertNotNull($request->getBody());
        $this->assertTrue($request->getBody() instanceof StreamInterface);
	}

	public function test_host_header_automatically_created_from_uri()
	{
		$request = new Request("get", "http://example.org");

		$this->assertEquals(
			['example.org'],
			$request->getHeader('Host')
		);
	}
}