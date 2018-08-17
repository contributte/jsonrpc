<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\GenericException;

use Gamee\JsonRPC\Response\Enum\GenericCodes;

/**
 * Invalid method parameter(s).
 */
final class InvalidParamsException extends GenericException implements IJsonRPCAwareException
{

	public function getErrorCode(): int
	{
		return GenericCodes::CODE_INVALID_PARAMS;
	}


	public function getGeneralMessage(): string
	{
		return 'Invalid params';
	}
}
