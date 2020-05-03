<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Command;

use Contributte\JsonRPC\Command\Exception\CommandNotFoundException;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;

class CommandProvider
{

	private Container $container;

	/**
	 * @var string[]
	 */
	private array $commandMapping = [];


	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	public function addCommandClass(string $commandName, string $commandClass): void
	{
		$this->commandMapping[$commandName] = $commandClass;
	}


	/**
	 * @throws CommandNotFoundException
	 */
	public function getCommandByName(string $commandName): ICommand
	{
		if (!isset($this->commandMapping[$commandName])) {
			throw new CommandNotFoundException("Unknown command [$commandName]");
		}

		$commandClass = $this->commandMapping[$commandName];

		try {
			$command = $this->container->getByType($commandClass);
		} catch (MissingServiceException $e) {
			throw new CommandNotFoundException("Unknown command [$commandClass]", $e->getCode(), $e);
		}

		if (!$command instanceof ICommand) {
			throw new CommandNotFoundException(
				"Command [$commandClass] is not an instance of " . ICommand::class
			);
		}

		return $command;
	}
}
