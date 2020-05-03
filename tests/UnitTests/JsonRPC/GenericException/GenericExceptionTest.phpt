<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\UnitTests\JsonRPC\GenericException;

use Contributte\JsonRPC\GenericException\InvalidParamsException;
use Contributte\JsonRPC\GenericException\InvalidRequestException;
use Contributte\JsonRPC\GenericException\MethodNotFoundException;
use Contributte\JsonRPC\GenericException\ParseErrorException;
use Contributte\JsonRPC\GenericException\ServerErrorException;
use Contributte\JsonRPC\Response\Enum\GenericCodes;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class GenericExceptionTest extends TestCase
{

	public function testInvalidParamsException(): void
	{
		$e = new InvalidParamsException('Foo');

		Assert::same(GenericCodes::CODE_INVALID_PARAMS, $e->getErrorCode());
		Assert::same('Invalid params', $e->getGeneralMessage());
		Assert::same('Foo', $e->getMessage());
	}


	public function testInvalidRequestException(): void
	{
		$e = new InvalidRequestException('Foo');

		Assert::same(GenericCodes::CODE_INVALID_REQUEST, $e->getErrorCode());
		Assert::same('Invalid request', $e->getGeneralMessage());
		Assert::same('Foo', $e->getMessage());
	}


	public function testMethodNotFoundException(): void
	{
		$e = new MethodNotFoundException('Foo');

		Assert::same(GenericCodes::CODE_METHOD_NOT_FOUND, $e->getErrorCode());
		Assert::same('Method not found', $e->getGeneralMessage());
		Assert::same('Foo', $e->getMessage());
	}


	public function testParseErrorException(): void
	{
		$e = new ParseErrorException('Foo');

		Assert::same(GenericCodes::CODE_PARSE_ERROR, $e->getErrorCode());
		Assert::same('Parse error', $e->getGeneralMessage());
		Assert::same('Foo', $e->getMessage());
	}


	public function testServerErrorException(): void
	{
		$e = new ServerErrorException('Foo');

		Assert::same(GenericCodes::CODE_INTERNAL_ERROR, $e->getErrorCode());
		Assert::same('Internal error', $e->getGeneralMessage());
		Assert::same('Foo', $e->getMessage());
	}
}

(new GenericExceptionTest())->run();
