<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\GenericException;

use Gamee\JsonRPC\Response\Enum\GenericCodes;

/**
 * Invalid JSON was received by the server.
 * An error occurred on the server while parsing the JSON text.
 */
final class ParseErrorException extends GenericException implements IJsonRPCAwareException
{

	public function getErrorCode(): int
	{
		return GenericCodes::CODE_PARSE_ERROR;
	}


	public function getGeneralMessage(): string
	{
		return 'Parse error';
	}
}
