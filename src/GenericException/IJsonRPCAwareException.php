<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\GenericException;

interface IJsonRPCAwareException extends \Throwable
{

	public function getErrorCode(): int;

	public function getGeneralMessage(): string;

}
