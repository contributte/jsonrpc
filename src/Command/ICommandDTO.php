<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Command;

interface ICommandDTO
{

	/**
	 * @param  mixed $parameters
	 */
	public static function fromValidParams($parameters): ICommandDTO;
}
