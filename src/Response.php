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
     * Response phrase for status code.
     *
     * @var string
     */
    protected $statusPhrase;

    /**
     * Response constructor.
     *
     * @param string $statusCode
     * @param StreamInterface|string $body
     * @param array $headers
     * @param string $httpVersion
     */
    public function __construct($statusCode = null, $body = null, array $headers = [], $httpVersion = "1.1")
    {
        if( $statusCode ){
            $this->statusCode = (int) $statusCode;
            $this->statusPhrase = ResponseStatus::getPhrase($this->statusCode) ?? "";
        }

        $this->body = ($body instanceof StreamInterface) ? $body : new BufferStream((string) $body);

        if( $headers ){
            $this->setHeaders($headers);
        }
        
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
        $instance->statusCode = $code;
        $instance->statusPhrase = ResponseStatus::getPhrase($code) ?? $reasonPhrase;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase()
    {
        return $this->statusPhrase;
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