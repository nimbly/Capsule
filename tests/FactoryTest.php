<?php

namespace Capsule\Tests;

use Capsule\Factory;
use Capsule\Request;
use Capsule\Response;
use Capsule\ServerRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\Factory
 * @covers Capsule\ServerRequest
 * @covers Capsule\Request
 * @covers Capsule\Response
 * @covers Capsule\Uri
 * @covers Capsule\MessageAbstract
 * @covers Capsule\Stream\BufferStream
 */
class FactoryTest extends TestCase
{
	public function test_server_request_factory()
	{
		$factory = new Factory;

		$serverRequest = $factory->createServerRequest("get", "http://example.org/path", ["server_param_1" => "server_value_1"]);

		$this->assertEquals(
			new ServerRequest("get", "http://example.org/path", null, [], [], [], [], ["server_param_1" => "server_value_1"]),
			$serverRequest
		);
	}

	public function test_request_factory()
	{
		$factory = new Factory;

		$request = $factory->createRequest("get", "http://example.org/path");

		$this->assertEquals(
			new Request("get", "http://example.org/path"),
			$request
		);
	}

	public function test_response_factory()
	{
		$factory = new Factory;

		$response = $factory->createResponse();

		$this->assertEquals(
			new Response(200),
			$response
		);
	}
}