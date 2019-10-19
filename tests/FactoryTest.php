<?php

namespace Capsule\Tests;

use Capsule\Factory;
use Capsule\Request;
use Capsule\Response;
use Capsule\ServerRequest;
use Capsule\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\Factory
 * @covers Capsule\ServerRequest
 * @covers Capsule\Request
 * @covers Capsule\Response
 * @covers Capsule\Uri
 * @covers Capsule\MessageAbstract
 * @covers Capsule\Stream\BufferStream
 * @covers Capsule\UploadedFile
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

	public function test_server_request_from_psr7_factory()
	{
		$factory = new Factory;

		$serverRequest = $factory->createServerRequestFromPsr7(
			new ServerRequest(
				"post",
				"http://example.org",
				['body1' => 'value1'],
				['query1' => 'value1'],
				['Content-Type' => 'application/json'],
				['cookie1' => 'value1'],
				[new UploadedFile('client_filename.txt', 'text/plain', 'tmp_filename', 8)],
				['server_param1' => 'value1']
			)
		);

		$this->assertEquals("POST", $serverRequest->getMethod());
		$this->assertEquals("http://example.org", (string) $serverRequest->getUri());
		$this->assertEquals(["body1" => "value1"], $serverRequest->getParsedBody());
	}
}