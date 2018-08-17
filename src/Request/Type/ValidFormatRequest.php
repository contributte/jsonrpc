<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Request\Type;

use Gamee\JsonRPC\Request\IRequest;

final class ValidFormatRequest implements IRequest
{

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var \stdClass
	 */
	private $params;

	/**
	 * @var string|null
	 */
	private $id;


	public function __construct(string $method, \stdClass $params, ?string $id)
	{
		$this->method = $method;
		$this->params = $params;
		$this->id = $id;
	}


	public function getMethod(): string
	{
		return $this->method;
	}


	public function getParams(): \stdClass
	{
		return $this->params;
	}


	public function getId(): ?string
	{
		return $this->id;
	}
}
