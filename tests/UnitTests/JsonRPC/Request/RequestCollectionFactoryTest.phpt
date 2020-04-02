<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\UnitTests\JsonRPC\Request;

use Gamee\JsonRPC\Request\RequestCollectionFactory;
use Gamee\JsonRPC\Request\Type\InvalidFormatRequest;
use Gamee\JsonRPC\Request\Type\ValidFormatRequest;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class RequestCollectionFactoryTest extends TestCase
{

	private RequestCollectionFactory $requestCollectionFactory;


	public function setUp(): void
	{
		$this->requestCollectionFactory = new RequestCollectionFactory;
	}


	public function testValidSingleRequest(): void
	{
		$collection = $this->requestCollectionFactory->create(
			file_get_contents(__DIR__ . '/input/validSingleRequest.json')
		);

		foreach ($collection as $request) {
			Assert::same(ValidFormatRequest::class, get_class($request));
			Assert::same('feed.getAll', $request->getMethod());

			$params = (array) $request->getParams();
			$params['pagination'] = (array) $params['pagination'];

			Assert::same(
				['pagination' => ['offset' => 1000, 'limit' => 100]],
				$params
			);
			Assert::same('asdfghjklzxcvbnmqwertyuiop', $request->getId());
		}

		Assert::false($collection->isBatchedRequest());
	}


	public function testValidSingleRequestInArray(): void
	{
		$collection = $this->requestCollectionFactory->create(
			file_get_contents(__DIR__ . '/input/validSingleRequestInArray.json')
		);

		Assert::true($collection->isBatchedRequest());
	}


	public function testInvalidBadgeRequest(): void
	{
		$collection = $this->requestCollectionFactory->create(
			file_get_contents(__DIR__ . '/input/badgeRequest.json')
		);

		$collection->rewind();

		$request = $collection->current();

		Assert::same(InvalidFormatRequest::class, get_class($request));
		Assert::null($request->getId());

		$collection->next();
		$request = $collection->current();

		Assert::same(InvalidFormatRequest::class, get_class($request));
		Assert::same('adasdasdad', $request->getId());

		$collection->next();
		$request = $collection->current();

		Assert::same(InvalidFormatRequest::class, get_class($request));
		Assert::null($request->getId());

		$collection->next();
		$request = $collection->current();

		Assert::same(InvalidFormatRequest::class, get_class($request));
		Assert::same('foo', $request->getId());

		$collection->next();
		$request = $collection->current();

		Assert::same(InvalidFormatRequest::class, get_class($request));
		Assert::null($request->getId());

		$collection->next();
		$request = $collection->current();

		Assert::same(InvalidFormatRequest::class, get_class($request));
		Assert::same('foo', $request->getId());

		$collection->next();
		$request = $collection->current();

		Assert::same(InvalidFormatRequest::class, get_class($request));
		Assert::same('foo', $request->getId());

		Assert::true($collection->isBatchedRequest());
	}
}

(new RequestCollectionFactoryTest())->run();
