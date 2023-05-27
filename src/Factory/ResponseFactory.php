<?php

namespace Nimbly\Capsule\Factory;

use Nimbly\Capsule\Response;
use Nimbly\Capsule\ResponseStatus;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory implements ResponseFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createResponse(int $code = 200, string $reasonPhrase = ""): ResponseInterface
	{
		return new Response(
			statusCode: ResponseStatus::from($code),
			reasonPhrase: $reasonPhrase ?: null
		);
	}
}