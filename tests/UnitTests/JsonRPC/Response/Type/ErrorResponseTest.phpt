<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\UnitTests\JsonRPC\Response\Type;

use Gamee\JsonRPC\GenericException\InvalidParamsException;
use Gamee\JsonRPC\Response\Enum\GenericCodes;
use Gamee\JsonRPC\Response\Type\ErrorResponse;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class ErrorResponseTest extends TestCase
{

	public function testExceptionConstructor(): void
	{
		$errorResponse = new ErrorResponse(123456789, 'General test message', 'Test description');

		Assert::same(123456789, $errorResponse->getCode());
		Assert::same('General test message', $errorResponse->getGeneralMessage());
		Assert::same('Test description', $errorResponse->getDescription());

		$errorResponse = new ErrorResponse(1, 'Message');

		Assert::same('Message', $errorResponse->getGeneralMessage());
		Assert::same('Message', $errorResponse->getDescription());
	}


	public function testExceptionFactory(): void
	{
		$fooException = new \Exception('Foo exception');
		$jsonRpcAwareException = new InvalidParamsException('Test description');

		Assert::exception(function() use ($fooException): void {
			ErrorResponse::fromJsonRPCAwareException($fooException);
		}, \TypeError::class);

		$errorResponse = ErrorResponse::fromJsonRPCAwareException($jsonRpcAwareException);

		Assert::same(GenericCodes::CODE_INVALID_PARAMS, $errorResponse->getCode());
		Assert::same('Invalid params', $errorResponse->getGeneralMessage());
		Assert::same('Test description', $errorResponse->getDescription());
	}
}

(new ErrorResponseTest())->run();
