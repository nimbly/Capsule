<?php

namespace Nimbly\Capsule\Factory;

use Nimbly\Capsule\Request;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * With this factory you can generate Request instances.
 */
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