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
     * Response constructor.
     *
     * @param int $statusCode
     * @param StreamInterface|string $body
     * @param array<string, string> $headers
     * @param string $httpVersion
     */
    public function __construct(int $statusCode, $body = null, array $headers = [], $httpVersion = "1.1")
    {
		$this->statusCode = $statusCode;
        $this->body = ($body instanceof StreamInterface) ? $body : new BufferStream((string) $body);
        $this->setHeaders($headers);
        $this->version = $httpVersion;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $instance = clone $this;
        $instance->statusCode = (int) $code;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase()
    {
        return ResponseStatus::getPhrase($this->statusCode) ?? "";
    }

    /**
     * Response is a successful one.
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return ($this->statusCode < 400);
    }
}