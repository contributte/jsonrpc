<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Response;

use Damejidlo\DateTimeFactory\DateTimeImmutableFactory;
use Gamee\JsonRPC\Request\IRequest;
use Gamee\JsonRPC\Request\RequestCollection;
use Gamee\JsonRPC\Response\Enum\GenericCodes;
use Gamee\JsonRPC\Response\Type\ErrorResponse;
use Gamee\JsonRPC\Response\Type\SuccessResponse;
use InvalidArgumentException;

class ResponseDataBuilder implements IResponseDataBuilder
{

	/**
	 * @var DateTimeImmutableFactory
	 */
	private $dateTimeImmutableFactory;


	public function __construct(?DateTimeImmutableFactory $dateTimeImmutableFactory = null)
	{
		if ($dateTimeImmutableFactory === null) {
			$dateTimeImmutableFactory = new DateTimeImmutableFactory;
		}

		$this->dateTimeImmutableFactory = $dateTimeImmutableFactory;
	}


	/**
	 * @inheritdoc
	 */
	public function buildResponseBadge(RequestCollection $requestCollection): array
	{
		if (!$requestCollection->isBatchedRequest()) {
			$requestCollection->rewind();
			$request = $requestCollection->current();

			$response = $requestCollection[$request];

			if ($response === null) {
				throw new \UnexpectedValueException;
			}

			return $this->buildResponse($request, $response);
		}

		$return = [];

		foreach ($requestCollection as $request) {
			$response = $requestCollection[$request];

			if ($response === null) {
				throw new \UnexpectedValueException;
			}

			$return[] = $this->buildResponse($request, $response);
		}

		return $return;
	}


	/**
	 * @inheritdoc
	 */
	public function buildServerError(): array
	{
		return [
			'jsonrpc' => '2.0',
			'error' => [
				'code' => GenericCodes::CODE_INTERNAL_ERROR,
				'message' => 'Server error',
				'data' => [
					'reason' => 'Server error',
				],
			],
			'id' => null,
			'time' => ($this->dateTimeImmutableFactory->getNow())->format(DATE_ATOM),
		];
	}


	/**
	 * @inheritdoc
	 */
	public function buildParseError(string $errorMessage): array
	{
		return [
			'jsonrpc' => '2.0',
			'error' => [
				'code' => GenericCodes::CODE_PARSE_ERROR,
				'message' => 'Parse error',
				'data' => [
					'reason' => $errorMessage,
				],
			],
			'id' => null,
			'time' => ($this->dateTimeImmutableFactory->getNow())->format(DATE_ATOM),
		];
	}


	/**
	 * @throws InvalidArgumentException
	 * @return array|mixed[]
	 */
	private function buildResponse(IRequest $request, IResponse $response): array
	{
		if ($response instanceof SuccessResponse) {
			return [
				'jsonrpc' => '2.0',
				'result' => $response->getResult() ?: new \stdClass,
				'id' => $request->getId(),
				'time' => ($this->dateTimeImmutableFactory->getNow())->format(DATE_ATOM),
			];
		}

		if ($response instanceof ErrorResponse) {
			return [
				'jsonrpc' => '2.0',
				'error' => [
					'code' => $response->getCode(),
					'message' => $response->getGeneralMessage(),
					'data' => [
						'reason' => $response->getDescription(),
					],
				],
				'id' => $request->getId(),
				'time' => ($this->dateTimeImmutableFactory->getNow())->format(DATE_ATOM),
			];
		}

		throw new InvalidArgumentException('Unknown response type');
	}
}
