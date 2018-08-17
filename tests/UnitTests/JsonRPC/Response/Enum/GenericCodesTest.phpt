<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\UnitTests\JsonRPC\Response\Enum;

use Gamee\JsonRPC\Response\Enum\GenericCodes;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class GenericCodesTest extends TestCase
{

	public function testInvalidParamsException(): void
	{
		Assert::same(-32700, GenericCodes::CODE_PARSE_ERROR);
		Assert::same(-32600, GenericCodes::CODE_INVALID_REQUEST);
		Assert::same(-32601, GenericCodes::CODE_METHOD_NOT_FOUND);
		Assert::same(-32602, GenericCodes::CODE_INVALID_PARAMS);
		Assert::same(-32603, GenericCodes::CODE_INTERNAL_ERROR);
	}
}

(new GenericCodesTest())->run();
