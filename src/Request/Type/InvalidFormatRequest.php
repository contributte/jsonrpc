<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Request\Type;

use Gamee\JsonRPC\Request\IRequest;

final class InvalidFormatRequest implements IRequest
{

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var string|null
	 */
	private $id;


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
