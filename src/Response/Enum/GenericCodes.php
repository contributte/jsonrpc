<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Response\Enum;

final class GenericCodes
{

	public const CODE_PARSE_ERROR = -32700;
	public const CODE_INVALID_REQUEST = -32600;
	public const CODE_METHOD_NOT_FOUND = -32601;
	public const CODE_INVALID_PARAMS = -32602;
	public const CODE_INTERNAL_ERROR = -32603;

}
