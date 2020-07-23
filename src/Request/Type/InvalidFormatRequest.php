<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Request\Type;

use Contributte\JsonRPC\Request\IRequest;

final class InvalidFormatRequest implements IRequest
{

	private string $message;
	private ?string $id = null;


	public function __construct(string $message, ?string $id = null)
	{
		$this->message = $message;
		$this->id = $id;
	}


	public function getMessage(): string
	{
		return $this->message;
	}


	public function getId(): ?string
	{
		return $this->id;
	}
}
