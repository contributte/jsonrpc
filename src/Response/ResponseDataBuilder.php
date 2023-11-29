<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Response;

use Contributte\JsonRPC\Request\IRequest;
use Contributte\JsonRPC\Request\RequestCollection;
use Contributte\JsonRPC\Response\Enum\GenericCodes;
use Contributte\JsonRPC\Response\Type\ErrorResponse;
use Contributte\JsonRPC\Response\Type\SuccessResponse;
use InvalidArgumentException;

class ResponseDataBuilder implements IResponseDataBuilder
{

	/**
	 * {@inheritdoc}
	 */
	public function buildResponseBadge(RequestCollection $requestCollection): array
	{
		if (!$requestCollection->isBatchedRequest()) {
			$requestCollection->rewind();
			$request = $requestCollection->current();

			$response = $requestCollection[$request];

			if ($response === null) {
				throw new \UnexpectedValueException();
			}

			return $this->buildResponse($request, $response);
		}

		$return = [];

		foreach ($requestCollection as $request) {
			$response = $requestCollection[$request];

			if ($response === null) {
				throw new \UnexpectedValueException();
			}

			$return[] = $this->buildResponse($request, $response);
		}

		return $return;
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function buildParseError(string $errorMessage): array
	{
		return $this->buildErrorResponse(
			GenericCodes::CODE_PARSE_ERROR,
			'Parse error',
			$errorMessage
		);
	}

	/**
	 * {@inheritdoc}
	 */
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
			'time' => (new \DateTimeImmutable())->format(DATE_ATOM),
		];
	}

	/**
	 * @return array<mixed>
	 */
	protected function buildResponse(IRequest $request, IResponse $response): array
	{
		if ($response instanceof SuccessResponse) {
			return [
				'jsonrpc' => '2.0',
				'result' => $response->getResult() ?? new \stdClass(),
				'id' => $request->getId(),
				'time' => (new \DateTimeImmutable())->format(DATE_ATOM),
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
