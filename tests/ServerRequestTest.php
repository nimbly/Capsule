<?php

namespace Capsule\Tests;

use Capsule\ServerRequest;
use Capsule\UploadedFile;
use Capsule\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\ServerRequest
 * @covers Capsule\Request
 * @covers Capsule\Uri
 * @covers Capsule\Stream\BufferStream
 * @covers Capsule\MessageAbstract
 * @covers Capsule\UploadedFile
 */
class ServerRequestTest extends TestCase
{
	public function makeRequest(): ServerRequest
	{
		return new ServerRequest(
			"GET",
			"http://example.org/foo/bar?q=search",
			'{"name": "Test User", "email": "test@example.com"}',
			[
				"query1" => "value1",
				"query2" => "value2"
			],
			[
				"Content-Type" => "application/json",
				"User-Agent" => "Capsule 1.0"
			],
			[
				"cookie1" => "value1",
				"cookie2" => "value2"
			],
			[
				new UploadedFile(
					'file1',
					'text/plain',
					'temp_file.name',
					100,
					UPLOAD_ERR_OK
				)
			],
			[],
			"1.1"
		);
	}

	public function test_create_with_uri_instance()
	{
		$uri = Uri::createFromString("http://example.org/foo/bar?q=search");

		$request = new ServerRequest("get", $uri);

		$this->assertSame(
			$uri,
			$request->getUri()
		);
	}

	public function test_create_with_array_body()
	{
		$request = new ServerRequest(
			"get",
			"http://example.org/foo/bar?q=search",
			["email" => "test@example.com", "name" => "Testy Test"]
		);

		$this->assertEquals(
			[
				"email" => "test@example.com",
				"name" => "Testy Test"
			],
			$request->getParsedBody()
		);
	}

	public function test_create_with_object_body()
	{
		$request = new ServerRequest(
			"get",
			"http://example.org/foo/bar?q=search",
			(object) ["email" => "test@example.com", "name" => "Testy Test"]
		);

		$this->assertEquals(
			[
				"email" => "test@example.com",
				"name" => "Testy Test"
			],
			$request->getParsedBody()
		);
	}

	public function test_create_with_json_content_type()
	{
		$request = new ServerRequest(
			"get",
			"http://example.org/foo/bar?q=search",
			'{"name": "Testy Test", "email": "test@example.com"}',
			[],
			[
				'Content-Type' => 'application/json'
			]
		);

		$this->assertEquals(
			[
				"name" => "Testy Test",
				"email" => "test@example.com"
			],
			$request->getParsedBody()
		);
	}

	public function test_create_with_form_encoded_content_type()
	{
		$request = new ServerRequest(
			"get",
			"http://example.org/foo/bar?q=search",
			"name=Testy+Test&email=test@example.com",
			[],
			[
				'Content-Type' => 'application/x-www-form-urlencoded'
			]
		);

		$this->assertEquals(
			[
				"name" => "Testy Test",
				"email" => "test@example.com"
			],
			$request->getParsedBody()
		);
	}

	public function test_create_with_no_content_type_header()
	{
		$request = new ServerRequest(
			"get",
			"http://example.org/foo/bar?q=search",
			"name=Testy+Test&email=test@example.com"
		);

		$this->assertNull(
			$request->getParsedBody()
		);
	}

	public function test_get_server_params()
	{
		$request = $this->makeRequest();

		$request = new ServerRequest(
			"get",
			"http://example.org/foo/bar?q=search",
			null,
			[],
			[],
			[],
			[],
			[
				"SERVER_NAME" => "Capsule",
				"SERVER_VERSION" => 1.0,
			]
		);

		$this->assertEquals(
			[
				"SERVER_NAME" => "Capsule",
				"SERVER_VERSION" => 1.0,
			],
			$request->getServerParams()
		);
	}

	public function test_get_cookie_params()
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				"cookie1" => "value1",
				"cookie2" => "value2"
			],
			$request->getCookieParams()
		);
	}

	public function test_with_cookie_params()
	{
		$request = $this->makeRequest();

		$request = $request->withCookieParams([
			'cookie3' => 'value3',
			'cookie4' => 'value4'
		]);

		$this->assertEquals(
			[
				'cookie3' => 'value3',
				'cookie4' => 'value4'
			],
			$request->getCookieParams()
		);
	}

	public function test_get_query_params()
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				"query1" => "value1",
				"query2" => "value2"
			],
			$request->getQueryParams()
		);
	}

	public function test_with_query_params()
	{
		$request = $this->makeRequest();

		$newRequest = $request->withQueryParams([
			"query3" => "value3",
			"query4" => "value4"
		]);

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			[
				"query3" => "value3",
				"query4" => "value4"
			],
			$newRequest->getQueryParams()
		);
	}

	public function test_get_uploaded_files()
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				new UploadedFile(
					'file1',
					'text/plain',
					'temp_file.name',
					100,
					UPLOAD_ERR_OK
				)
			],

			$request->getUploadedFiles()
		);
	}

	public function test_with_uploaded_files_is_immutable()
	{
		$request = $this->makeRequest();

		$newRequest = $request->withUploadedFiles([
			new UploadedFile("example1.file", "appliication/json", "foo", 101, UPLOAD_ERR_OK),
			new UploadedFile("example2.file", "appliication/json", "bar", 201, UPLOAD_ERR_NO_FILE)
		]);

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			[
				new UploadedFile("example1.file", "appliication/json", "foo", 101, UPLOAD_ERR_OK),
				new UploadedFile("example2.file", "appliication/json", "bar", 201, UPLOAD_ERR_NO_FILE)
			],
			$newRequest->getUploadedFiles()
		);
	}

	public function test_with_parsed_body()
	{
		$request = $this->makeRequest();

		$newRequest = $request->withParsedBody(["foo" => "bar"]);

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			[
				"foo" => "bar"
			],
			$newRequest->getParsedBody()
		);
	}

	public function test_with_attribute()
	{
		$request = $this->makeRequest();

		$newRequest = $request->withAttribute("attr1", "value1");

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			"value1",
			$newRequest->getAttribute("attr1")
		);
	}

	public function test_get_attributes()
	{
		$request = $this->makeRequest();

		$request = $request->withAttribute("attr1", "value1");
		$request = $request->withAttribute("attr2", "value2");

		$this->assertEquals(
			[
				"attr1" => "value1",
				"attr2" => "value2"
			],
			$request->getAttributes()
		);
	}

	public function test_without_attribute()
	{
		$request = $this->makeRequest();

		$request = $request->withAttribute("attr1", "value1");
		$request = $request->withAttribute("attr2", "value2");

		$this->assertEquals(
			[
				"attr1" => "value1",
				"attr2" => "value2"
			],
			$request->getAttributes()
		);

		$request = $request->withoutAttribute("attr2");

		$this->assertEquals(
			[
				"attr1" => "value1"
			],
			$request->getAttributes()
		);
	}

	public function test_create_from_globals()
	{
		$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
		$_SERVER['REQUEST_METHOD'] = "POST";
		$_SERVER['REQUEST_URI'] = "/foo?query1=value1";
		$_SERVER['HTTP_HOST'] = "capsule.org";
		$_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
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

		$request = ServerRequest::createFromGlobals();

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
			"application/json",
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
				new UploadedFile('file1.json', 'text/plain', 'test.json', 100, UPLOAD_ERR_OK)
			],
			$request->getUploadedFiles()
		);

		$this->assertEquals(
			$_SERVER,
			$request->getServerParams()
		);
	}

	public function test_has()
	{
		$request = $this->makeRequest();

		$this->assertTrue($request->has('name'));
		$this->assertTrue($request->has('query1'));
	}

	public function test_get()
	{
		$request = $this->makeRequest();

		$this->assertEquals('value1', $request->get('query1'));
		$this->assertEquals('test@example.com', $request->get('email'));
	}

	public function test_get_prefers_parsed_body()
	{
		$request = $this->makeRequest();
		$request = $request->withQueryParams([
			'email' => 'bob@example.com'
		]);

		$this->assertEquals(
			'test@example.com',
			$request->get('email')
		);
	}
}