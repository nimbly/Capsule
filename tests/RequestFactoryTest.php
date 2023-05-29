<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\RequestFactory;
use Nimbly\Capsule\Request;
use PHPUnit\Framework\TestCase;

/**
 * @covers Nimbly\Capsule\Factory\RequestFactory
 * @covers Nimbly\Capsule\Request
 * @covers Nimbly\Capsule\Factory\UriFactory
 * @covers Nimbly\Capsule\Uri
 * @covers Nimbly\Capsule\MessageAbstract
 * @covers Nimbly\Capsule\Stream\BufferStream
 * @covers Nimbly\Capsule\Factory\StreamFactory
 */
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