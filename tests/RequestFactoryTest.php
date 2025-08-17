<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequestFactory::class)]
class RequestFactoryTest extends TestCase
{
	public function test_create_request_with_string_uri(): void
	{
		$requestFactory = new RequestFactory;
		$request = $requestFactory->createRequest("get", "/api/books");

		$this->assertInstanceOf(Request::class, $request);
		$this->assertEquals("GET", $request->getMethod());
		$this->assertEquals("/api/books", $request->getUri()->getPath());
	}
}