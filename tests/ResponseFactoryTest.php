<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\ResponseFactory;
use Nimbly\Capsule\Response;
use Nimbly\Capsule\ResponseStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseFactory::class)]
class ResponseFactoryTest extends TestCase
{
	public function test_create_response(): void
	{
		$responseFactory = new ResponseFactory;

		$response = $responseFactory->createResponse(200);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(ResponseStatus::OK->value, $response->getStatusCode());
		$this->assertEquals(ResponseStatus::OK->getPhrase(), $response->getReasonPhrase());
	}

	public function test_create_response_with_reasonphrase(): void
	{
		$responseFactory = new ResponseFactory;

		$response = $responseFactory->createResponse(
			ResponseStatus::NOT_FOUND->value,
			"Resource not found"
		);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(ResponseStatus::NOT_FOUND->value, $response->getStatusCode());
		$this->assertEquals("Resource not found", $response->getReasonPhrase());
	}
}