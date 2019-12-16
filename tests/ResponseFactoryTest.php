<?php

namespace Capsule\Tests;

use Capsule\Factory\ResponseFactory;
use Capsule\Response;
use Capsule\ResponseStatus;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\Factory\ResponseFactory
 * @covers Capsule\Response
 * @covers Capsule\ResponseStatus
 * @covers Capsule\MessageAbstract
 * @covers Capsule\Stream\BufferStream
 */
class ResponseFactoryTest extends TestCase
{
	public function test_create_response()
	{
		$responseFactory = new ResponseFactory;

		$response = $responseFactory->createResponse(200);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals(ResponseStatus::getPhrase(200), $response->getReasonPhrase());
	}

	public function test_create_response_with_reasonphrase()
	{
		$responseFactory = new ResponseFactory;

		$response = $responseFactory->createResponse(404, "Resource not found");

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(404, $response->getStatusCode());
		$this->assertEquals("Resource not found", $response->getReasonPhrase());
	}
}