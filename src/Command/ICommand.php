<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Command;

use Gamee\JsonRPC\Response\IResponse;

interface ICommand
{

	public function getCommandDTOClass(): string;

	public function execute(ICommandDTO $commandDTO): IResponse;
}
