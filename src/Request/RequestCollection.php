<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Request;

use Gamee\JsonRPC\Response\IResponse;

final class RequestCollection extends \SplObjectStorage
{

	/**
	 * @var bool
	 */
	private $isBatchedRequest = true;


	public function current(): IRequest
	{
		$request = parent::current();

		if (!$request instanceof IRequest) {
			throw new \UnexpectedValueException;
		}

		return $request;
	}


	/**
	 * @todo after 7.2 add static type IRequest
	 * @param IRequest $object
	 */
	public function offsetGet($object): ?IResponse
	{
		return parent::offsetGet($object);
	}


	public function isBatchedRequest(): bool
	{
		return $this->isBatchedRequest;
	}


	public function setIsBatchedRequest(bool $isBatchedRequest = true): void
	{
		$this->isBatchedRequest = $isBatchedRequest;
	}
}
