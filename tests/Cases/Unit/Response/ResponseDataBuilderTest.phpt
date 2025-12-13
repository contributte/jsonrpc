<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\Response;

use Contributte\JsonRPC\Request\IRequest;
use Contributte\JsonRPC\Request\RequestCollection;
use Contributte\JsonRPC\Request\Type\ValidFormatRequest;
use Contributte\JsonRPC\Response\Enum\GenericCodes;
use Contributte\JsonRPC\Response\IResponse;
use Contributte\JsonRPC\Response\ResponseDataBuilder;
use Contributte\JsonRPC\Response\Type\SuccessResponse;
use Mockery;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
class ResponseDataBuilderTest extends TestCase
{

	private ResponseDataBuilder $responseDataBuilder;

	public function setUp(): void
	{
		$this->responseDataBuilder = new ResponseDataBuilder();
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
		];

		$actual = $this->responseDataBuilder->buildParseError('Foo');
		unset($actual['time']);

		Assert::same($expected, $actual);
	}

	public function testUnknownResponseType(): void
	{
		$collection = $this->createSingleRequestCollection();

		Assert::exception(function () use ($collection): void {
			$this->responseDataBuilder->buildResponseBadge($collection);
		}, \InvalidArgumentException::class, 'Unknown response type');
	}

	public function testBuildSingleEmptyResponse(): void
	{
		$expected = [
			'jsonrpc' => '2.0',
			'result' => new \stdClass(),
			'id' => 'iddddddddd',
		];

		$collection = $this->createSingleRequestCollection(
			new ValidFormatRequest('foo', new \stdClass(), 'iddddddddd'),
			new SuccessResponse(null)
		);

		$data = $this->responseDataBuilder->buildResponseBadge($collection);

		Assert::true(isset($data['result']));
		Assert::true($data['result'] instanceof \stdClass);

		unset($data['result'], $data['time'], $expected['result']);

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
		];

		$collection = $this->createSingleRequestCollection(
			new ValidFormatRequest('foo', new \stdClass(), 'iddddddddd'),
			new SuccessResponse($result)
		);

		$data = $this->responseDataBuilder->buildResponseBadge($collection);
		unset($data['time']);

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

		$collection->offsetSet($request);
		$collection[$request] = $response;

		$collection->setIsBatchedRequest(false);

		return $collection;
	}

}

(new ResponseDataBuilderTest())->run();
