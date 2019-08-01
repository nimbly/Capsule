<?php declare(strict_types=1);

namespace Capsule;

use Psr\Http\Message\UriInterface;


class Uri implements UriInterface
{
    /**
     * URI scheme.
     *
     * @var string
     */
    protected $scheme;

    /**
     * Request host.
     *
     * @var string
     */
    protected $host;

    /**
     * URI port number.
     *
     * @var int|null
     */
    protected $port;

    /**
     * URI username.
     *
     * @var string
     */
    protected $username;

    /**
     * URI password
     *
     * @var string
     */
    protected $password;

    /**
     * URI path
     *
     * @var string
     */
    protected $path;

    /**
     * URI query
     *
     * @var string
     */
    protected $query;

    /**
     * URI fragment
     *
     * @var string
     */
    protected $fragment;

    /**
     * URI constructor.
     *
     * @param string|null $url
     */
    public function __construct($url = null)
    {
        if( $url ){
            $scheme = "(https?)\:\/\/";
            $authorization = "(\w+)\:(\w+)?@";
            $host = "[a-z0-9\-\.]+";
            $port = "\:([0-9]+)";
            $path = "\/[^\?#]*\/?";
            $query = "(?:[\w\[\]\_]+\=[^&^#]+&?)+";
            $fragment = "#([0-9a-zA-Z\!\$&'\(\)\*\+\,;\=\-\.\_\~\:\@\/\?]+)";

            \preg_match("/^(?:{$scheme})(?:{$authorization})?({$host})(?:{$port})?({$path})?(?:\?({$query}))?(?:{$fragment})?$/i", $url, $match, PREG_UNMATCHED_AS_NULL);

            // Check that supplied URI is valid.
            if( empty($match[1]) ||
                empty($match[4]) ){
                throw new \Exception("Invalid URI");
            }

            $this->scheme = \strtolower($match[1]);
            $this->username = $match[2] ?? "";
            $this->password = $match[3] ?? "";
            $this->host = \strtolower($match[4]);
            $this->port = (int) ($match[5] ?? $this->derivePortFromScheme($this->scheme));
            $this->path = $match[6] ?? "/";
            $this->query = $match[7] ?? "";
            $this->fragment = $match[8] ?? "";
        }
    }

    /**
     * Given a scheme, derive the port number to use.
     *
     * @param string $scheme
     * @return int
     */
    private function derivePortFromScheme($scheme): int
    {
        if( \strtolower($scheme) === 'https' ){
            return 443;
        }

        return 80;
    }

    /**
     * @inheritDoc
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        if( empty($this->username) && empty($this->password) ){
            return "";
        }

        return "{$this->username}:{$this->password}@{$this->host}:{$this->port}";
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        $userInfo = "";

        if( $this->username ){
            $userInfo = $this->username;
        }

        if( $this->password ){
            $userInfo .= ":{$this->password}";
        }

        return $userInfo;
    }

    /**
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritDoc
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        $instance = clone $this;
        $instance->scheme = \strtolower($scheme);
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $instance = clone $this;
        $instance->username = $user;
        $instance->password = $password ?? "";
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        $instance = clone $this;
        $instance->host = $host;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        $instance = clone $this;
        $instance->port = $port;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        $instance = clone $this;
        $instance->path = $path;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        $instance = clone $this;
        $instance->query = $query;
        return $instance;
    }


    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        $instance = clone $this;
        $instance->fragment = $fragment;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $url = "{$this->scheme}://";

        if( $this->username || $this->password ){
            $url .= "{$this->username}:{$this->password}@";
        }

        $url .= $this->host;

        if( $this->port ){
            $url .= ":{$this->port}";
        }

        $url.=$this->path;

        if( $this->query ){
            $url .= "?{$this->query}";
        }

        if( $this->fragment ){
            $url .= "#{$this->fragment}";
        }

        return $url;
    }
}