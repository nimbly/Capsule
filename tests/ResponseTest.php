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
        $response = new Response(ResponseStatus::OK);

		$this->assertEquals(
			ResponseStatus::getPhrase(ResponseStatus::OK),
			$response->getReasonPhrase()
		);
    }

    public function test_with_status_code_saves_data()
    {
        $response = new Response(ResponseStatus::OK);
        $response = $response->withStatus(ResponseStatus::NOT_FOUND, "Page Not Found");

        $this->assertEquals(ResponseStatus::NOT_FOUND, $response->getStatusCode());
        $this->assertEquals("Page Not Found", $response->getReasonPhrase());
    }

    public function test_with_status_code_resolves_phrase_if_none_given()
    {
        $response = new Response(ResponseStatus::NOT_FOUND);
        $this->assertEquals(ResponseStatus::getPhrase(ResponseStatus::NOT_FOUND), $response->getReasonPhrase());
    }

    public function test_with_status_code_is_immutable()
    {
        $response = new Response(ResponseStatus::OK);
        $newResponse = $response->withStatus(ResponseStatus::NOT_FOUND);
        $this->assertNotSame($response, $newResponse);
	}

    public function test_constructor()
    {
        $response = new Response(
            ResponseStatus::CREATED,
            new BufferStream("OK"),
            [
                "Content-Type" => "text/plain",
			],
			"Reason Phrase",
            "2"
        );

        $this->assertEquals(ResponseStatus::CREATED, $response->getStatusCode());
        $this->assertEquals("OK", $response->getBody()->getContents());
		$this->assertEquals("text/plain", $response->getHeader('Content-Type')[0]);
		$this->assertEquals("Reason Phrase", $response->getReasonPhrase());
        $this->assertEquals("2", $response->getProtocolVersion());
	}

	public function test_constructor_defaults()
	{
		$response = new Response(ResponseStatus::OK);

        $this->assertEmpty($response->getBody()->getContents());
		$this->assertEquals([], $response->getHeaders());
		$this->assertEquals(ResponseStatus::getPhrase(ResponseStatus::OK), $response->getReasonPhrase());
        $this->assertEquals("1.1", $response->getProtocolVersion());
	}

    public function test_no_body_provided_in_constructor_creates_a_body()
    {
        $response = new Response(ResponseStatus::OK);

        $this->assertNotNull($response->getBody());
        $this->assertTrue($response->getBody() instanceof StreamInterface);
    }
}