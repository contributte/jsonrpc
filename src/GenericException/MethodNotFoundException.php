<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\GenericException;

use Gamee\JsonRPC\Response\Enum\GenericCodes;

/**
 * The method does not exist / is not available.
 */
final class MethodNotFoundException extends GenericException implements IJsonRPCAwareException
{

	public function getErrorCode(): int
	{
		return GenericCodes::CODE_METHOD_NOT_FOUND;
	}


	public function getGeneralMessage(): string
	{
		return 'Method not found';
	}
}
