<?php declare(strict_types=1);

namespace Capsule\Factory;

use Capsule\Request;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class RequestFactory implements RequestFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createRequest(string $method, $uri): RequestInterface
	{
		return new Request($method, $uri);
	}
}