<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Response;
use Nimbly\Capsule\ResponseStatus;
use Nimbly\Capsule\Stream\BufferStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers Nimbly\Capsule\Response
 * @covers Nimbly\Capsule\ResponseStatus
 * @covers Nimbly\Capsule\MessageAbstract
 * @covers Nimbly\Capsule\Stream\BufferStream
 * @covers Nimbly\Capsule\Factory\StreamFactory
 */
class ResponseTest extends TestCase
{
	public function test_reason_phrase_set_on_constructor(): void
	{
		$response = new Response(ResponseStatus::OK);

		$this->assertEquals(
			ResponseStatus::OK->getPhrase(),
			$response->getReasonPhrase()
		);
	}

	public function test_with_status_code_saves_data(): void
	{
		$response = new Response(ResponseStatus::OK);

		$response = $response->withStatus(
			ResponseStatus::NOT_FOUND->value,
			"Page Not Found"
		);

		$this->assertEquals(
			ResponseStatus::NOT_FOUND->value,
			$response->getStatusCode()
		);

		$this->assertEquals(
			"Page Not Found",
			$response->getReasonPhrase()
		);
	}

	public function test_with_status_code_resolves_phrase_if_none_given(): void
	{
		$response = new Response(ResponseStatus::NOT_FOUND);

		$this->assertEquals(
			ResponseStatus::NOT_FOUND->getPhrase(),
			$response->getReasonPhrase()
		);
	}

	public function test_with_status_code_is_immutable(): void
	{
		$response = new Response(ResponseStatus::OK);
		$newResponse = $response->withStatus(ResponseStatus::NOT_FOUND->value);
		$this->assertNotSame($response, $newResponse);
	}

	public function test_constructor(): void
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

		$this->assertEquals(ResponseStatus::CREATED->value, $response->getStatusCode());
		$this->assertEquals("OK", $response->getBody()->getContents());
		$this->assertEquals("text/plain", $response->getHeader("Content-Type")[0]);
		$this->assertEquals("Reason Phrase", $response->getReasonPhrase());
		$this->assertEquals("2", $response->getProtocolVersion());
	}

	public function test_constructor_defaults(): void
	{
		$response = new Response(ResponseStatus::OK);

		$this->assertEmpty($response->getBody()->getContents());
		$this->assertEquals([], $response->getHeaders());
		$this->assertEquals(ResponseStatus::OK->getPhrase(), $response->getReasonPhrase());
		$this->assertEquals("1.1", $response->getProtocolVersion());
	}

	public function test_no_body_provided_in_constructor_creates_a_body(): void
	{
		$response = new Response(ResponseStatus::OK);

		$this->assertNotNull($response->getBody());
		$this->assertTrue($response->getBody() instanceof StreamInterface);
	}
}