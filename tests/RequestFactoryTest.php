<?php

namespace Capsule\Tests;

use Capsule\Factory\RequestFactory;
use Capsule\Request;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\Factory\RequestFactory
 * @covers Capsule\Request
 * @covers Capsule\Factory\UriFactory
 * @covers Capsule\Uri
 * @covers Capsule\MessageAbstract
 * @covers Capsule\Stream\BufferStream
 */
class RequestFactoryTest extends TestCase
{
	public function test_create_request_with_string_uri()
	{
		$requestFactory = new RequestFactory;
		$request = $requestFactory->createRequest("get", "/api/books");

		$this->assertInstanceOf(Request::class, $request);
		$this->assertEquals("GET", $request->getMethod());
		$this->assertEquals("/api/books", $request->getUri()->getPath());
	}
}