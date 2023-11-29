<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Command;

use Contributte\JsonRPC\Response\IResponse;

interface ICommand
{

	public function getCommandDTOClass(): string;

	public function execute(ICommandDTO $commandDTO): IResponse;

}
