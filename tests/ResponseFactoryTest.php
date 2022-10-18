<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\ResponseFactory;
use Nimbly\Capsule\Response;
use Nimbly\Capsule\ResponseStatus;
use PHPUnit\Framework\TestCase;

/**
 * @covers Nimbly\Capsule\Factory\ResponseFactory
 * @covers Nimbly\Capsule\Factory\StreamFactory
 * @covers Nimbly\Capsule\Response
 * @covers Nimbly\Capsule\ResponseStatus
 * @covers Nimbly\Capsule\MessageAbstract
 * @covers Nimbly\Capsule\Stream\BufferStream
 */
class ResponseFactoryTest extends TestCase
{
	public function test_create_response()
	{
		$responseFactory = new ResponseFactory;

		$response = $responseFactory->createResponse(200);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(ResponseStatus::OK, $response->getStatusCode());
		$this->assertEquals(ResponseStatus::getPhrase(ResponseStatus::OK), $response->getReasonPhrase());
	}

	public function test_create_response_with_reasonphrase()
	{
		$responseFactory = new ResponseFactory;

		$response = $responseFactory->createResponse(
			ResponseStatus::NOT_FOUND,
			"Resource not found"
		);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(ResponseStatus::NOT_FOUND, $response->getStatusCode());
		$this->assertEquals("Resource not found", $response->getReasonPhrase());
	}
}