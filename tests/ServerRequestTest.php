<?php

namespace Capsule\Tests;

use Capsule\Factory\UriFactory;
use Capsule\ServerRequest;
use Capsule\Stream\BufferStream;
use Capsule\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\ServerRequest
 * @covers Capsule\Request
 * @covers Capsule\Factory\UriFactory
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
			['name' => "Test User", "email" => "test@example.com"],
			//'{"name": "Test User", "email": "test@example.com"}',
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
		$uri = UriFactory::createFromString("http://example.org/foo/bar?q=search");

		$request = new ServerRequest("get", $uri);

		$this->assertSame(
			$uri,
			$request->getUri()
		);
	}

	public function test_create_with_string_body_creates_buffer_stream()
	{
		$request = new ServerRequest(
			"post",
			"http://example.org/foo/bar?q=search",
			\json_encode(["email" => "test@example.com", "name" => "Testy Test"])
		);

		$this->assertInstanceOf(
			BufferStream::class,
			$request->getBody()
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
			(object) [
				"email" => "test@example.com",
				"name" => "Testy Test"
			],
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
				"query2" => "value2",
				"q" => "search"
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

	public function test_has_body_param()
	{
		$request = $this->makeRequest();

		$this->assertTrue($request->hasBodyParam('name'));
	}

	public function test_has_query_param()
	{
		$request = $this->makeRequest();

		$this->assertTrue($request->hasQueryParam('query1'));
	}

	public function test_get_query_param()
	{
		$request = $this->makeRequest();

		$this->assertEquals('value1', $request->getQueryParam('query1'));
	}

	public function test_get_body_param_from_array_parsed_body()
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			[
				"email" => "test@nimbly.io"
			]
		);

		$this->assertEquals("test@nimbly.io", $request->getBodyParam('email'));
	}

	public function test_get_body_param_from_object_parsed_body()
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			(object) [
				"email" => "test@nimbly.io"
			]
		);

		$this->assertEquals("test@nimbly.io", $request->getBodyParam('email'));
	}

	public function test_get_body_param_returns_null_if_not_found()
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			[
				"email" => "test@nimbly.io"
			]
		);

		$this->assertNull($request->getBodyParam('id'));
	}

	public function test_only_body_params()
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			[
				"email" => "test@nimbly.io",
				"name" => "Bob Smith",
				"age" => 42
			]
		);

		$this->assertEquals(
			[
				"name" => "Bob Smith",
				"age" => 42
			],
			$request->onlyBodyParams(["name", "age"])
		);
	}

	public function test_except_body_params()
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			[
				"email" => "test@nimbly.io",
				"name" => "Bob Smith",
				"age" => 42
			]
		);

		$this->assertEquals(
			[
				"email" => "test@nimbly.io",
				"name" => "Bob Smith"
			],
			$request->exceptBodyParams(["age"])
		);
	}

	public function test_has_uploaded_file()
	{
		$request = $this->makeRequest();

		$uploadedFile = new UploadedFile("Ok");

		$request = $request->withUploadedFiles([
			"file" => $uploadedFile
		]);

		$this->assertTrue(
			$request->hasUploadedFile("file")
		);
	}

	public function test_get_uploaded_file()
	{
		$request = $this->makeRequest();

		$uploadedFile = new UploadedFile("Ok");

		$request = $request->withUploadedFiles([
			"file" => $uploadedFile
		]);

		$this->assertSame(
			$uploadedFile,
			$request->getUploadedFile("file")
		);
	}

	public function test_get_all_params()
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				'name' => 'Test User',
				'email' => 'test@example.com',
				"query1" => "value1",
				"query2" => "value2",
				"q" => "search"
			],
			$request->getAllParams()
		);
	}
}