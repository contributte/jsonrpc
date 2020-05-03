<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Request;

use Contributte\JsonRPC\Request\Exception\RequestCollectionCreationException;

interface IRequestCollectionFactory
{

	/**
	 * @throws RequestCollectionCreationException
	 */
	public function create(string $rawRequest): RequestCollection;
}
