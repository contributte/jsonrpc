<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Request\Type;

use Contributte\JsonRPC\Request\IRequest;

final class ValidFormatRequest implements IRequest
{

	private string $method;

	private \stdClass $params;

	private ?string $id = null;


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
