<?php

namespace Nimbly\Capsule;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
	public function __construct(
		protected ?string $scheme = null,
		protected ?string $host = null,
		protected ?string $path = null,
		protected ?int $port = null,
		protected ?string $username = null,
		protected ?string $password = null,
		protected ?string $query = null,
		protected ?string $fragment = null
	)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function getScheme(): string
	{
		return $this->scheme ?? "";
	}

	/**
	 * @inheritDoc
	 */
	public function getAuthority(): string
	{
		if( empty($this->username) && empty($this->password) ){
			return "";
		}

		return \sprintf(
			"%s:%s@%s:%s",
			$this->username ?? "",
			$this->password ?? "",
			$this->host ?? "",
			$this->port ?? ""
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getUserInfo(): string
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
	public function getHost(): string
	{
		return $this->host ?? "";
	}

	/**
	 * @inheritDoc
	 */
	public function getPort(): ?int
	{
		return $this->port;
	}

	/**
	 * @inheritDoc
	 */
	public function getPath(): string
	{
		return $this->path ?? "";
	}

	/**
	 * @inheritDoc
	 */
	public function getQuery(): string
	{
		return $this->query ?? "";
	}

	/**
	 * @inheritDoc
	 */
	public function getFragment(): string
	{
		return $this->fragment ?? "";
	}

	/**
	 * @inheritDoc
	 */
	public function withScheme($scheme): static
	{
		$instance = clone $this;
		$instance->scheme = \strtolower($scheme);
		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function withUserInfo($user, $password = null): static
	{
		$instance = clone $this;
		$instance->username = $user;
		$instance->password = $password ?? "";
		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function withHost($host): static
	{
		$instance = clone $this;
		$instance->host = $host;
		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function withPort($port): static
	{
		$instance = clone $this;
		$instance->port = $port;
		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function withPath($path): static
	{
		$instance = clone $this;
		$instance->path = $path;
		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function withQuery($query): static
	{
		$instance = clone $this;
		$instance->query = $query;
		return $instance;
	}


	/**
	 * @inheritDoc
	 */
	public function withFragment($fragment): static
	{
		$instance = clone $this;
		$instance->fragment = $fragment;
		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function __toString(): string
	{
		$url = "";

		if( $this->scheme ){
			$url .= ($this->scheme . ":");
		}

		if( $this->getAuthority() ){
			$url .= ("//" . $this->getAuthority());
		}
		else {
			$url .= ("//" . $this->host);

			if( $this->port ){
				$url .= (":" . $this->port);
			}
		}

		if( empty($this->path) && $this->getAuthority() ){
			$url .= "/";
		}
		elseif( $this->path && !$this->getAuthority() ){
			$url .= ("/" . \trim($this->path, "/"));
		}
		else {
			$url .= (string) $this->path;
		}

		if( $this->query ){
			$url .= ("?" . $this->query);
		}

		if( $this->fragment ){
			$url .= ("#" . $this->fragment);
		}

		return $url;
	}
}