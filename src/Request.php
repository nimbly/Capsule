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
    public function __construct(?string $method = null, $uri = null, $body = null, array $headers = null, string $httpVersion = "1.1")
    {
        if( $method ){
            $this->method = strtoupper($method);
        }

        $this->uri = $uri instanceof UriInterface ? $uri : new Uri((string) $uri);
        $this->body = $body instanceof StreamInterface ? $body : new BufferStream((string) $body);

        if( $headers ){
            $this->setHeaders($headers);
        }

        $this->version = $httpVersion;
    }

    /**
     * Create a Request from the PHP $_SERVER global.
     *
     * @return Request
     */
    public static function makeFromGlobals(): Request
    {
        preg_match('/^HTTP\/(.+)$/i', $_SERVER['SERVER_PROTOCOL'] ?? '', $serverProtocol);

        $request = new static(
            $_SERVER['REQUEST_METHOD'] ?? 'get',
            new Uri(
                (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') .
                (isset($_SERVER['PHP_AUTH_USER']) ? ($_SERVER['PHP_AUTH_USER'] . ':' . ($_SERVER['PHP_AUTH_PW'] ?? '') . '@') : '') .
                $_SERVER['HTTP_HOST'] .
                $_SERVER['REQUEST_URI']
            ),
            new FileStream(fopen('php://input', 'r')),
            \getallheaders(),
            $serverProtocol[1] ?? '1.1'
        );

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