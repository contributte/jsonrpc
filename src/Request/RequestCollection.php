<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Request;

use Gamee\JsonRPC\Response\IResponse;

final class RequestCollection extends \SplObjectStorage
{

	private bool $isBatchedRequest = true;


	public function current(): IRequest
	{
		$request = parent::current();

		if (!$request instanceof IRequest) {
			throw new \UnexpectedValueException;
		}

		return $request;
	}


	/**
	 * {@inheritDoc}
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
