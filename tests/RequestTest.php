<?php

namespace Capsule\Tests;

use Capsule\Request;
use Capsule\Stream\BufferStream;
use Capsule\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers Capsule\Request
 * @covers Capsule\Uri
 * @covers Capsule\Stream\BufferStream
 * @covers Capsule\Stream\FileStream
 * @covers Capsule\MessageAbstract
 */
class RequestTest extends TestCase
{
    public function test_with_method_saves_data()
    {
        $request = (new Request)->withMethod("post");
        $this->assertEquals("POST", $request->getMethod());
    }

    public function test_with_method_is_immutable()
    {
        $request = new Request;
        $newRequest = $request->withMethod("post");

        $this->assertEmpty($request->getMethod());
        $this->assertNotEquals($request, $newRequest);
    }

    public function test_with_uri_saves_data()
    {
        $uri = Uri::createFromString("https://www.example.com");
        $request = (new Request)->withUri($uri);
        $this->assertEquals($uri, $request->getUri());
    }

    public function test_with_uri_is_immutable()
    {
        $request = new Request;
        $newRequest = $request->withUri(Uri::createFromString("https://example.com"));

        $this->assertNotSame($request, $newRequest);
    }

    public function test_with_request_target_saves_data()
    {
        $request = (new Request)
        ->withRequestTarget("GET example.com:443 HTTP/1.1");

        $this->assertEquals("GET example.com:443 HTTP/1.1", $request->getRequestTarget());
	}

    public function test_with_request_target_is_immutable()
    {
        $request = new Request;
        $newRequest = $request->withRequestTarget("GET example.com:443 HTTP/1.1");

        $this->assertNotEquals($request, $newRequest);
	}

	public function test_building_request_target_if_none_provided()
	{
		$request = new Request("get", "http://example.com/sample?q=search");

		$this->assertEquals(
			"/sample?q=search",
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
        $this->assertEquals("http://example.com:80", (string) $request->getUri());
        $this->assertEquals("BODY", $request->getBody()->getContents());
        $this->assertEquals("en_US", $request->getHeader("Accept-Language")[0]);
        $this->assertEquals("2", $request->getProtocolVersion());
    }

    public function test_uri_instance_created_automatically_if_not_provided()
    {
        $request = new Request("get");

        $this->assertNotNull($request->getUri());
        $this->assertTrue($request->getUri() instanceof Uri);
    }

    public function test_body_instance_created_automatically_if_not_provided()
    {
        $request = new Request("get", "http://example.com:80/");

        $this->assertNotNull($request->getBody());
        $this->assertTrue($request->getBody() instanceof StreamInterface);
    }
}