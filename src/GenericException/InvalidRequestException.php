<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\GenericException;

use Gamee\JsonRPC\Response\Enum\GenericCodes;

/**
 * The JSON sent is not a valid Request object.
 */
final class InvalidRequestException extends GenericException implements IJsonRPCAwareException
{

	public function getErrorCode(): int
	{
		return GenericCodes::CODE_INVALID_REQUEST;
	}


	public function getGeneralMessage(): string
	{
		return 'Invalid request';
	}
}
