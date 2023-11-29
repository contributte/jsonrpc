<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Request;

use Contributte\JsonRPC\GenericException\IJsonRPCAwareException;
use Contributte\JsonRPC\Request\Type\ValidFormatRequest;
use Contributte\JsonRPC\Response\IResponse;

interface IRequestProcessor
{

	/**
	 * @throws IJsonRPCAwareException
	 */
	public function process(ValidFormatRequest $request): IResponse;

}
