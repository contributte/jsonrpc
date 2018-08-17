<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\UnitTests\JsonRPC\Response;

use Damejidlo\DateTimeFactory\DateTimeImmutableFactory;
use Gamee\JsonRPC\Request\IRequest;
use Gamee\JsonRPC\Request\RequestCollection;
use Gamee\JsonRPC\Request\Type\ValidFormatRequest;
use Gamee\JsonRPC\Response\Enum\GenericCodes;
use Gamee\JsonRPC\Response\IResponse;
use Gamee\JsonRPC\Response\IResponseDataBuilder;
use Gamee\JsonRPC\Response\ResponseDataBuilder;
use Gamee\JsonRPC\Response\Type\SuccessResponse;
use Mockery;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class ResponseDataBuilderTest extends TestCase
{

	/**
	 * @var IResponseDataBuilder
	 */
	private $responseDataBuilder;


	public function setUp(): void
	{
		$dateTimeImmutableFactory = new class() extends DateTimeImmutableFactory
		{

			public function getNow(): \DateTimeImmutable
			{
				return \DateTimeImmutable::createFromFormat(DATE_ATOM, '2018-03-24T11:36:19+01:00');
			}
		};

		$this->responseDataBuilder = new ResponseDataBuilder($dateTimeImmutableFactory);
	}


	public function testBuildParseError(): void
	{
		$expected = [
			'jsonrpc' => '2.0',
			'error' => [
				'code' => GenericCodes::CODE_PARSE_ERROR,
				'message' => 'Parse error',
				'data' => [
					'reason' => 'Foo',
				],
			],
			'id' => null,
			'time' => '2018-03-24T11:36:19+01:00',
		];

		Assert::same($expected, $this->responseDataBuilder->buildParseError('Foo'));
	}


	public function testUnknownResponseType(): void
	{
		$collection = $this->createSingleRequestCollection();

		Assert::exception(function() use ($collection): void {
			$this->responseDataBuilder->buildResponseBadge($collection);
		}, \InvalidArgumentException::class, 'Unknown response type');
	}


	public function testBuildSingleEmptyResponse(): void
	{
		$expected = [
			'jsonrpc' => '2.0',
			'result' => new \stdClass,
			'id' => 'iddddddddd',
			'time' => '2018-03-24T11:36:19+01:00',
		];

		$collection = $this->createSingleRequestCollection(
			new ValidFormatRequest('foo', new \stdClass(), 'iddddddddd'),
			new SuccessResponse(null)
		);

		$data = $this->responseDataBuilder->buildResponseBadge($collection);

		Assert::true(isset($data['result']));
		Assert::true($data['result'] instanceof \stdClass);

		unset($data['result'], $expected['result']);

		Assert::same($expected, $data);
	}


	public function testBuildSingleNonEmptyResponse(): void
	{
		$result = new \stdClass();
		$result->foo = 'bar';

		$expected = [
			'jsonrpc' => '2.0',
			'result' => $result,
			'id' => 'iddddddddd',
			'time' => '2018-03-24T11:36:19+01:00',
		];



		$collection = $this->createSingleRequestCollection(
			new ValidFormatRequest('foo', new \stdClass(), 'iddddddddd'),
			new SuccessResponse($result)
		);

		$data = $this->responseDataBuilder->buildResponseBadge($collection);

		Assert::same($expected, $data);
	}


	private function createSingleRequestCollection(
		?IRequest $request = null,
		?IResponse $response = null
	): RequestCollection
	{
		$request = $request ?: Mockery::mock(IRequest::class)
			->shouldReceive('getId()')->andReturn('iddddddddd')
			->getMock();

		$response = $response ?: Mockery::mock(IResponse::class);

		$collection = new RequestCollection();

		$collection->attach($request);
		$collection[$request] = $response;

		$collection->setIsBatchedRequest(false);

		return $collection;
	}
}

(new ResponseDataBuilderTest())->run();
