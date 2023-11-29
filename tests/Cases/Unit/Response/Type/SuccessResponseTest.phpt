<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Response\Type;

use Contributte\JsonRPC\Response\Type\SuccessResponse;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
class SuccessResponseTest extends TestCase
{

	public function testResponse(): void
	{
		$response = new SuccessResponse(null);

		Assert::null($response->getResult());

		$result = new \stdClass();
		$result->foo = 'bar';

		$response = new SuccessResponse($result);

		Assert::same($result, $response->getResult());
	}
}

(new SuccessResponseTest())->run();
