<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\GenericException;

abstract class GenericException extends \Exception
{

	abstract public function getErrorCode(): int;

	abstract public function getGeneralMessage(): string;
}
