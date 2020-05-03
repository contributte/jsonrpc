<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Command;

interface ICommandDTO
{

	public static function fromValidParams(\stdClass $parameters): ICommandDTO;
}
