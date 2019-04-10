<?php declare(strict_types=1);

namespace Capsule;

use Capsule\Stream\BufferStream;
use Capsule\Stream\FileStream;
use Capsule\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;


class Request extends MessageAbstract implements RequestInterface
{
    /**
     * HTTP method
     *
     * @var string
     */
    protected $method;

    /**
     * Request URI
     *
     * @var UriInterface
     */
    protected $uri;

    /**
     * Request target form
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param StreamInterface|string $body
     * @param array $headers
     * @param string $httpVersion
     */
    public function __construct($method = null, $uri = null, $body = null, array $headers = null, $httpVersion = "1.1")
    {
        if( $method ){
            $this->method = strtoupper($method);
        }

        if( $uri ){
            $this->uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        }

        if( $body ){
            $this->body = $body instanceof StreamInterface ? $body : new BufferStream((string) $body);
        }

        if( $headers ){
            $this->setHeaders($headers);
        }

        $this->version = $httpVersion;
    }

    /**
     * Make a new Request instance.
     *
     * @param string $method
     * @param UriInterface $uri
     * @param StreamInterface $body
     * @param array $headers
     * @return Request
     */
    public static function make(string $method, UriInterface $uri, ?StreamInterface $body = null, ?array $headers = null, string $version = "1.1"): Request
    {
        $request = (new Request)
        ->withMethod($method)
        ->withUri($uri)
        ->withBody($body ?? new BufferStream)
        ->withProtocolVersion($version);
        
        $request->setHeaders($headers ?? []);

        return $request;
    }

    /**
     * Create a Request from the PHP $_SERVER global.
     *
     * @return Request
     */
    public static function makeFromGlobals(): Request
    {
        $request = new Request(
            $_SERVER['REQUEST_METHOD'] ?? 'get',
            new Uri(
                (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
                (isset($_SERVER['PHP_AUTH_USER']) ? ($_SERVER['PHP_AUTH_USER'] . ':' . ($_SERVER['PHP_AUTH_PW'] ?? '') . '@') : '') .
                $_SERVER['HTTP_HOST'] .
                $_SERVER['REQUEST_URI']
            ),
            new FileStream(fopen('php://input', 'r'))
        );

        if( preg_match('/^HTTP\/(.+)$/i', $_SERVER['SERVER_PROTOCOL'] ?? '', $match) ){
            $request = $request->withProtocolVersion($match[1]);
        }

        return $request;
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method) : Request
    {
        $instance = clone $this;
        $instance->method = strtoupper($method);
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $instance = clone $this;
        $instance->uri = $uri;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getRequestTarget()
    {
        return $this->requestTarget;
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget)
    {
        $instance = clone $this;
        $instance->requestTarget = $requestTarget;
        return $instance;
    }
}