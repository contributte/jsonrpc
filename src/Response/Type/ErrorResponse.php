<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Response\Type;

use Gamee\JsonRPC\GenericException\IJsonRPCAwareException;
use Gamee\JsonRPC\Response\IResponse;

final class ErrorResponse implements IResponse
{

	/**
	 * @var int
	 */
	private $code;

	/**
	 * @var string
	 */
	private $generalMessage;

	/**
	 * @var string
	 */
	private $description;


	public function __construct(
		int $code,
		string $generalMessage,
		?string $description = null
	) {
		$this->code = $code;
		$this->generalMessage = $generalMessage;
		$this->description = $description === null ? $generalMessage : $description;
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
