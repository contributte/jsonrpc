<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Request;

use Gamee\JsonRPC\Request\Exception\RequestCollectionCreationException;

interface IRequestCollectionFactory
{

	/**
	 * @throws RequestCollectionCreationException
	 */
	public function create(string $rawRequest): RequestCollection;
}
