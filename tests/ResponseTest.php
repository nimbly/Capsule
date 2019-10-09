<?php

namespace Capsule\Tests;

use Capsule\Response;
use Capsule\ResponseStatus;
use Capsule\Stream\BufferStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers Capsule\Response
 * @covers Capsule\ResponseStatus
 * @covers Capsule\MessageAbstract
 * @covers Capsule\Stream\BufferStream
 */
class ResponseTest extends TestCase
{
    public function test_reason_phrase_set_on_constructor()
    {
        $response = new Response(200);
        $this->assertNotEmpty($response->getReasonPhrase());
    }

    public function test_with_status_code_saves_data()
    {
        $response = new Response(200);
        $response = $response->withStatus(404, "Page Not Found");

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals("Not Found", $response->getReasonPhrase());
    }

    public function test_with_status_code_resolves_phrase_if_none_given()
    {
        $response = new Response(404);
        $this->assertEquals(ResponseStatus::getPhrase(404), $response->getReasonPhrase());
    }

    public function test_with_status_code_is_immutable()
    {
        $response = new Response(200);
        $newResponse = $response->withStatus(404);
        $this->assertNotSame($response, $newResponse);
	}

    public function test_constructor()
    {
        $response = new Response(
            201,
            new BufferStream("OK"),
            [
                "Content-Type" => "text/plain",
            ],
            "2"
        );

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals("OK", $response->getBody()->getContents());
        $this->assertEquals("text/plain", $response->getHeader('Content-Type')[0]);
        $this->assertEquals(2, $response->getProtocolVersion());
    }

    public function test_no_body_provided_in_constructor_creates_a_body()
    {
        $response = new Response(200);

        $this->assertNotNull($response->getBody());
        $this->assertTrue($response->getBody() instanceof StreamInterface);
    }
}