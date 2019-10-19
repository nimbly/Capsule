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

	/**
	 * Create a Capsule ServerRequest instance from another PSR-7 ServerRequestInterface instance.
	 *
	 * @param ServerRequestInterface $serverRequest
	 * @return ServerRequest
	 */
	public function createServerRequestFromPsr7(ServerRequestInterface $psr7ServerRequest): ServerRequest
	{
		return new ServerRequest(
			$psr7ServerRequest->getMethod(),
			$psr7ServerRequest->getUri(),
			$psr7ServerRequest->getParsedBody() ?? $psr7ServerRequest->getBody(),
			$psr7ServerRequest->getQueryParams(),
			$psr7ServerRequest->getHeaders(),
			$psr7ServerRequest->getCookieParams(),
			$psr7ServerRequest->getUploadedFiles(),
			$psr7ServerRequest->getServerParams(),
			$psr7ServerRequest->getProtocolVersion()
		);
	}
}