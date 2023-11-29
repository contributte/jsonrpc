<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Request;

use Contributte\JsonRPC\Response\IResponse;

/**
 * @extends \SplObjectStorage<IRequest, IResponse|null>
 */
final class RequestCollection extends \SplObjectStorage
{

	private bool $isBatchedRequest = true;

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
