<?php declare(strict_types=1);

namespace Capsule;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Capsule\Stream\BufferStream;


class Response extends MessageAbstract implements ResponseInterface
{
    /**
     * Response status code.
     *
     * @var int
     */
	protected $statusCode;

	/**
	 * Response reason phrase.
	 *
	 * @var string
	 */
	protected $reasonPhrase;

    /**
     * Response constructor.
     *
     * @param int $statusCode
     * @param StreamInterface|string $body
     * @param array<string,string> $headers
	 * @param string|null $reasonPhrase
     * @param string $httpVersion
     */
    public function __construct(int $statusCode, $body = null, array $headers = [], ?string $reasonPhrase = null, string $httpVersion = "1.1")
    {
		$this->statusCode = $statusCode;
        $this->body = $body instanceof StreamInterface ? $body : new BufferStream((string) $body);
		$this->setHeaders($headers);
		$this->reasonPhrase = $reasonPhrase ?: ResponseStatus::getPhrase($statusCode) ?? "";
        $this->version = $httpVersion;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
	 * @param int $code
	 * @param string $reasonPhrase
	 * @return static
     */
    public function withStatus($code, $reasonPhrase = ''): Response
    {
        $instance = clone $this;
		$instance->statusCode = $code;
		$instance->reasonPhrase = $reasonPhrase ? $reasonPhrase : ResponseStatus::getPhrase($code) ?? "";
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}