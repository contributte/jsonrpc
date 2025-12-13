<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Request;

use Contributte\JsonRPC\Request\Exception\RequestCollectionCreationException;
use Contributte\JsonRPC\Request\Type\InvalidFormatRequest;
use Contributte\JsonRPC\Request\Type\ValidFormatRequest;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

final class RequestCollectionFactory implements IRequestCollectionFactory
{

	/**
	 * @throws RequestCollectionCreationException
	 */
	public function create(string $rawRequest): RequestCollection
	{
		try {
			$requestData = Json::decode($rawRequest);
		} catch (JsonException $e) {
			throw new RequestCollectionCreationException('Invalid payload data - invalid json');
		}

		$collection = new RequestCollection();

		/**
		 * Map array of requests into RequestCollection
		 */
		if (is_array($requestData)) {
			/** @var mixed[] $oneRequestData */
			foreach ($requestData as $oneRequestData) {
				$collection->offsetSet(
					$this->createRequestFromRequestData($oneRequestData),
				);
			}
		} elseif ($requestData instanceof \stdClass) {
			/**
			 * Even when there is a single command, put it into RequestCollection
			 */
			$collection->offsetSet($this->createRequestFromRequestData($requestData));
			$collection->setIsBatchedRequest(false);
		} else {
			throw new RequestCollectionCreationException('Invalid payload data - invalid json');
		}

		return $collection;
	}

	/**
	 * @param array|mixed[]|\stdClass $requestData
	 */
	public function createRequestFromRequestData($requestData): IRequest
	{
		if (!$requestData instanceof \stdClass) {
			return $this->createInvalidRequest($requestData, 'Invalid JSON-RPC format');
		}

		if (!isset($requestData->method) || !is_string($requestData->method)) {
			return $this->createInvalidRequest($requestData, 'Invalid or missing [method] property');
		}

		if (!isset($requestData->jsonrpc) || $requestData->jsonrpc !== '2.0') {
			return $this->createInvalidRequest($requestData, 'Invalid or missing [jsonrpc] property');
		}

		if (!isset($requestData->params)) {
			return $this->createInvalidRequest($requestData, 'Missing [params] property');
		}

		if (isset($requestData->id)) {
			if (!is_string($requestData->id)) {
				return $this->createInvalidRequest($requestData, 'Invalid [id] format');
			}

			$id = $requestData->id;
		} else {
			$id = null;
		}

		return new ValidFormatRequest($requestData->method, $requestData->params, $id);
	}

	/**
	 * @param array|mixed[]|\stdClass $requestData
	 */
	private function createInvalidRequest(
		$requestData,
		string $errorMessage,
	): InvalidFormatRequest
	{
		if ($requestData instanceof \stdClass) {
			if (isset($requestData->id) && is_string($requestData->id)) {
				return new InvalidFormatRequest($errorMessage, $requestData->id);
			}
		}

		return new InvalidFormatRequest($errorMessage);
	}

}
