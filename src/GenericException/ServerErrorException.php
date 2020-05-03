<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\GenericException;

use Contributte\JsonRPC\Response\Enum\GenericCodes;

/**
 * Reserved for implementation-defined server-errors.
 */
final class ServerErrorException extends GenericException implements IJsonRPCAwareException
{

	public function getErrorCode(): int
	{
		return GenericCodes::CODE_INTERNAL_ERROR;
	}


	public function getGeneralMessage(): string
	{
		return 'Internal error';
	}
}
