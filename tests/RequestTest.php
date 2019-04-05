<?php

namespace Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Request;
use Capsule\Stream\BufferStream;
use Capsule\Uri;

/**
 * @covers Capsule\Request
 * @covers Capsule\Uri
 * @covers Capsule\Stream\BufferStream
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
        $uri = new Uri("https://www.example.com");
        $request = (new Request)->withUri($uri);
        $this->assertEquals($uri, $request->getUri());
    }

    public function test_with_uri_is_immutable()
    {
        $request = new Request;
        $newRequest = $request->withUri(new Uri("https://example.com"));

        $this->assertEmpty($request->getUri());
        $this->assertNotEquals($request, $newRequest);
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

    public function test_request_constructor()
    {
        $request = new Request(
            "post",
            "http://example.com",
            "OK",
            [
                "Accept-Language" => "en_US"
            ],
            2
        );

        $this->assertEquals("POST", $request->getMethod());
        $this->assertEquals("http://example.com:80/", (string) $request->getUri());
        $this->assertEquals("OK", $request->getBody()->getContents());
        $this->assertEquals("en_US", $request->getHeader("Accept-Language")[0]);
        $this->assertEquals(2, $request->getProtocolVersion());
    }

    public function test_make_factory()
    {
        $request = Request::make(
            "post",
            new Uri("http://example.com"),
            new BufferStream("OK"),
            [
                "Accept-Language" => "en_US"
            ],
            2);

        $this->assertTrue(($request instanceof Request));
    }
}