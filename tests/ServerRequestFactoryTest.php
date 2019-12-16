<?php

namespace Capsule\Tests;

use Capsule\Factory\ServerRequestFactory;
use Capsule\ServerRequest;
use Capsule\UploadedFile;
use GuzzleHttp\Psr7\ServerRequest as Psr7ServerRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\Factory\ServerRequestFactory
 * @covers Capsule\ServerRequest
 * @covers Capsule\Request
 * @covers Capsule\MessageAbstract
 * @covers Capsule\Factory\UploadedFileFactory
 * @covers Capsule\UploadedFile
 * @covers Capsule\Factory\UriFactory
 * @covers Capsule\Uri
 * @covers Capsule\Stream\BufferStream
 */
class ServerRequestFactoryTest extends TestCase
{
	public function test_create_server_request()
	{
		$serverRequestFactory = new ServerRequestFactory;

		$request = $serverRequestFactory->createServerRequest("get", "http://example.com", ["server_param1" => "value1"]);

		$this->assertInstanceOf(ServerRequest::class, $request);
		$this->assertEquals("GET", $request->getMethod());
		$this->assertEquals("example.com", $request->getUri()->getHost());
		$this->assertEquals('http', $request->getUri()->getScheme());
	}

	public function test_create_from_globals()
	{
		$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
		$_SERVER['REQUEST_METHOD'] = "POST";
		$_SERVER['REQUEST_URI'] = "/foo?query1=value1";
		$_SERVER['HTTP_HOST'] = "capsule.org";
		$_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
		$_SERVER['HTTP_X_FORWARDED_BY'] = '5.6.7.8';
		$_GET = ["query1" => "value1"];
		$_POST = ["post1" => "value1"];
		$_COOKIE = ["cookie1" => "value1"];

		$_FILES = [
			[
				'name' => 'file1.json',
				'type' => 'text/plain',
				'tmp_name' => 'test.json',
				'size' => 100,
				'error' => UPLOAD_ERR_OK
			]
		];

		$request = ServerRequestFactory::createFromGlobals();

		$this->assertEquals(
			'1.1',
			$request->getProtocolVersion()
		);

		$this->assertEquals(
			'POST',
			$request->getMethod()
		);

		$this->assertEquals(
			"capsule.org",
			$request->getHeaderLine("Host")
		);

		$this->assertEquals(
			"application/x-www-form-urlencoded",
			$request->getHeaderLine("Content-Type")
		);

		$this->assertEquals(
			"5.6.7.8",
			$request->getHeaderLine("X-Forwarded-By")
		);

		$this->assertEquals(
			['query1' => 'value1'],
			$request->getQueryParams()
		);

		$this->assertEquals(
			["cookie1" => "value1"],
			$request->getCookieParams()
		);

		$this->assertEquals(
			["post1" => "value1"],
			$request->getParsedBody()
		);

		$this->assertEquals(
			[
				new UploadedFile('test.json', 'file1.json', 'text/plain', 100, UPLOAD_ERR_OK)
			],
			$request->getUploadedFiles()
		);

		$this->assertEquals(
			$_SERVER,
			$request->getServerParams()
		);
	}

	public function test_create_from_psr7()
	{
		$serverRequestFactory = new ServerRequestFactory;

		$psr7request = new Psr7ServerRequest("get", "http://example.com", ["header1" => "value1"], "body_param1=value1&body_param2=value2", "1.1", ["server_param1" => "value1"]);

		$psr7request = $psr7request->withQueryParams([
			"query1" => "value1"
		])->withHeader("header1", "value1");

		$request = $serverRequestFactory->createServerRequestFromPsr7($psr7request);

		$this->assertInstanceOf(ServerRequest::class, $request);
		$this->assertEquals("GET", $request->getMethod());
		$this->assertEquals("http", $request->getUri()->getScheme());
		$this->assertEquals("example.com", $request->getUri()->getHost());
		$this->assertEquals([
			"query1" => "value1"
		], $request->getQueryParams());
		$this->assertEquals([
			"Host" => ["example.com"],
			"header1" => ["value1"]
		], $request->getHeaders());
		$this->assertEquals("body_param1=value1&body_param2=value2", $request->getBody()->getContents());
		$this->assertEquals("1.1", $request->getProtocolVersion());
		$this->assertEquals([
			"server_param1" => "value1"
		], $request->getServerParams());
	}
}

