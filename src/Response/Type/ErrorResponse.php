<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Response\Type;

use Contributte\JsonRPC\GenericException\IJsonRPCAwareException;
use Contributte\JsonRPC\Response\IResponse;

final class ErrorResponse implements IResponse
{

	private int $code;
	private string $generalMessage;
	private string $description;


	public function __construct(
		int $code,
		string $generalMessage,
		?string $description = null
	) {
		$this->code = $code;
		$this->generalMessage = $generalMessage;
		$this->description = $description ?? $generalMessage;
	}


	public static function fromJsonRPCAwareException(IJsonRPCAwareException $e): self
	{
		return new static(
			$e->getErrorCode(),
			$e->getGeneralMessage(),
			$e->getMessage()
		);
	}


	public function getCode(): int
	{
		return $this->code;
	}


	public function getGeneralMessage(): string
	{
		return $this->generalMessage;
	}


	public function getDescription(): string
	{
		return $this->description;
	}
}
