<?php declare(strict_types=1);

namespace Capsule;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;


abstract class MessageAbstract implements MessageInterface
{
    /**
     * Message version
     *
     * @var string
     */
    protected $version = "1.1";

    /**
     * Message headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Message body
     *
     * @var StreamInterface
     */
    protected $body;

    /**
     * @inheritDoc
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        if( !in_array($version, ["1.1", "1.0", "2", "2.0"]) ){
            throw new \Exception("Invalid protocol version {$version}");
        }

        $instance = clone $this;
        $instance->version = $version;
        return $instance;
    }

    /**
     * Find a header by its case-insensitive name.
     *
     * @param string $name
     * @param mixed $default
     * @return string|false
     */
    private function findHeaderKey($name)
    {
        foreach( $this->headers as $key => $value ){
            if( strtolower($name) === strtolower($key) ){
                return $key;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name)
    {
        return ($this->findHeaderKey($name) !== false);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        if( ($key = $this->findHeaderKey($name)) !== false ){
            return $this->headers[$key];
        }
        
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name)
    {
        $header = $this->getHeader($name);

        if( empty($header) ){
            return "";
        }

        return "{$name}: " . implode(",", $header);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        $instance = clone $this;
        $instance->headers[$name] = [$value];
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        if( ($key = $this->findHeaderKey($name)) === false ){
            $key = $name;
        }

        $instance = clone $this;
        $instance->headers[$key][] = $value;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        if( ($key = $this->findHeaderKey($name)) === false ){
            return $this;
        }

        $instance = clone $this;
        unset($instance->headers[$key]);
        return $instance;
    }

    /**
     * Mass assign headers.
     * 
     * This method is NOT immutable and *will* modify the current object.
     *
     * @param array $headers
     * @return void
     */
    protected function setHeaders(array $headers): void
    {
        foreach( $headers as $name => $value ){
            $this->headers[$name] = [$value];
        }
    }

    /**
     * @inheritDoc
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     * @return MessageInterface
     */
    public function withBody(StreamInterface $body)
    {
        $instance = clone $this;
        $instance->body = $body;
        return $instance;
    }
}