<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Response\Type;

use Contributte\JsonRPC\Response\IResponse;

final class SuccessResponse implements IResponse
{

	private ?\stdClass $result = null;


	public function __construct(?\stdClass $result)
	{
		$this->result = $result;
	}


	public function getResult(): ?\stdClass
	{
		return $this->result;
	}
}
