<?php declare(strict_types=1);

namespace Capsule;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class Factory implements RequestFactoryInterface, ServerRequestFactoryInterface, ResponseFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createRequest(string $method, $uri): RequestInterface
	{
		return new Request($method, $uri);
	}

	/**
	 * @inheritDoc
	 */
	public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
	{
		return new ServerRequest($method, $uri, null, [], [], [], [], $serverParams);
	}

	/**
	 * @inheritDoc
	 */
	public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
	{
		return new Response($code);
	}
}