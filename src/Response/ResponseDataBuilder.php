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
		return $this->buildErrorResponse(
			GenericCodes::CODE_INTERNAL_ERROR,
			'Server error',
			'Server error'
		);
	}


	/**
	 * @inheritdoc
	 */
	public function buildParseError(string $errorMessage): array
	{
		return $this->buildErrorResponse(
			GenericCodes::CODE_PARSE_ERROR,
			'Parse error',
			$errorMessage
		);
	}


	public function buildErrorResponse(
		int $code,
		string $message,
		string $reason,
		?string $id = null
	): array
	{
		return [
			'jsonrpc' => '2.0',
			'error' => [
				'code' => $code,
				'message' => $message,
				'data' => [
					'reason' => $reason,
				],
			],
			'id' => $id,
			'time' => $this->dateTimeImmutableFactory->getNow()->format(DATE_ATOM),
		];
	}


	/**
	 * @throws InvalidArgumentException
	 * @return array|mixed[]
	 */
	protected function buildResponse(IRequest $request, IResponse $response): array
	{
		if ($response instanceof SuccessResponse) {
			return [
				'jsonrpc' => '2.0',
				'result' => $response->getResult() ?: new \stdClass,
				'id' => $request->getId(),
				'time' => $this->dateTimeImmutableFactory->getNow()->format(DATE_ATOM),
			];
		}

		if ($response instanceof ErrorResponse) {
			return $this->buildErrorResponse(
				$response->getCode(),
				$response->getGeneralMessage(),
				$response->getDescription(),
				$request->getId()
			);
		}

		throw new InvalidArgumentException('Unknown response type');
	}
}
