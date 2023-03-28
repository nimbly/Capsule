<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\ServerRequestFactory;
use Nimbly\Capsule\ServerRequest;
use Nimbly\Capsule\UploadedFile;
use Nimbly\Capsule\Stream\BufferStream;
use Nimbly\Capsule\Stream\ResourceStream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers Nimbly\Capsule\MessageAbstract
 * @covers Nimbly\Capsule\Request
 * @covers Nimbly\Capsule\ServerRequest
 * @covers Nimbly\Capsule\UploadedFile
 * @covers Nimbly\Capsule\Uri
 * @covers Nimbly\Capsule\Stream\BufferStream
 * @covers Nimbly\Capsule\Stream\ResourceStream
 * @covers Nimbly\Capsule\Factory\ServerRequestFactory
 * @covers Nimbly\Capsule\Factory\StreamFactory
 * @covers Nimbly\Capsule\Factory\UploadedFileFactory
 * @covers Nimbly\Capsule\Factory\UriFactory
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
		$this->assertEquals("http", $request->getUri()->getScheme());
	}

	public function test_create_from_globals()
	{
		$_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
		$_SERVER["REQUEST_METHOD"] = "POST";
		$_SERVER["REQUEST_URI"] = "/foo?query1=value1";
		$_SERVER["HTTP_HOST"] = "capsule.org";
		$_SERVER["HTTP_CONTENT_TYPE"] = "application/x-www-form-urlencoded";
		$_SERVER["HTTP_X_FORWARDED_BY"] = "5.6.7.8";
		$_GET = ["query1" => "value1"];
		$_POST = ["post1" => "value1"];
		$_COOKIE = ["cookie1" => "value1"];

		$_FILES = [
			[
				"name" => "file1.json",
				"type" => "text/plain",
				"tmp_name" => __DIR__ . "/fixtures/test.json",
				"size" => 100,
				"error" => UPLOAD_ERR_OK
			]
		];

		$request = ServerRequestFactory::createFromGlobals();

		$this->assertEquals(
			"1.1",
			$request->getProtocolVersion()
		);

		$this->assertEquals(
			"POST",
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
			["query1" => "value1"],
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

		$this->assertCount(1, $request->getUploadedFiles());

		$this->assertEquals(
			"file1.json",
			$request->getUploadedFiles()[0]->getClientFilename()
		);

		$this->assertEquals(
			"text/plain",
			$request->getUploadedFiles()[0]->getClientMediaType()
		);

		$this->assertEquals(
			100,
			$request->getUploadedFiles()[0]->getSize()
		);

		$this->assertEquals(
			UPLOAD_ERR_OK,
			$request->getUploadedFiles()[0]->getError()
		);

		$this->assertEquals(
			"{\"name\": \"Test\", \"email\": \"test@example.com\"}",
			$request->getUploadedFiles()[0]->getStream()->getContents()
		);

		$this->assertEquals(
			$_SERVER,
			$request->getServerParams()
		);
	}

	public function test_create_from_psr7()
	{
		$serverRequestFactory = new ServerRequestFactory;

		$psr7request = new ServerRequest(
			method: "get",
			uri: "http://example.com",
			body: "body_param1=value1&body_param2=value2",
			headers: ["header1" => "value1"],
			serverParams: ["server_param1" => "value1"]
		);

		$psr7request = $psr7request->withQueryParams([
			"query1" => "value1"
		])->withHeader("header1", "value1");

		$request = $serverRequestFactory->createServerRequestFromPsr7($psr7request);

		$this->assertInstanceOf(ServerRequest::class, $request);
		$this->assertNotSame($psr7request, $request);
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

