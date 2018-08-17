<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Request;

use Gamee\JsonRPC\GenericException\IJsonRPCAwareException;
use Gamee\JsonRPC\Request\Type\ValidFormatRequest;
use Gamee\JsonRPC\Response\IResponse;

interface IRequestProcessor
{

	/**
	 * @throws IJsonRPCAwareException
	 */
	public function process(ValidFormatRequest $request): IResponse;
}
