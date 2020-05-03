<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Request;

use Contributte\JsonRPC\Command\CommandProvider;
use Contributte\JsonRPC\Command\Exception\CommandNotFoundException;
use Contributte\JsonRPC\Command\ICommand;
use Contributte\JsonRPC\Command\ICommandDTO;
use Contributte\JsonRPC\Exception\MissingSchemaException;
use Contributte\JsonRPC\Exception\SchemaValidatorException;
use Contributte\JsonRPC\GenericException\IJsonRPCAwareException;
use Contributte\JsonRPC\GenericException\InvalidParamsException;
use Contributte\JsonRPC\GenericException\MethodNotFoundException;
use Contributte\JsonRPC\GenericException\ServerErrorException;
use Contributte\JsonRPC\Request\Type\ValidFormatRequest;
use Contributte\JsonRPC\Response\IResponse;
use Contributte\JsonRPC\SchemeValidator;

class RequestProcessor implements IRequestProcessor
{

	private CommandProvider $commandProvider;

	private SchemeValidator $schemeValidator;


	public function __construct(CommandProvider $commandProvider, SchemeValidator $schemeValidator)
	{
		$this->commandProvider = $commandProvider;
		$this->schemeValidator = $schemeValidator;
	}


	/**
	 * @throws IJsonRPCAwareException
	 */
	public function process(ValidFormatRequest $request): IResponse
	{
		$command = $this->createCommand($request);

		$this->validateCommand($command, $request);

		$commandDTO = $this->createCommandDTO($command, $request);

		/**
		 * Execute command with belonging DTO
		 */
		return $command->execute($commandDTO);
	}


	/**
	 * @throws IJsonRPCAwareException
	 */
	protected function createCommand(ValidFormatRequest $request): ICommand
	{
		try {
			return $this->commandProvider->getCommandByName($request->getMethod());
		} catch (CommandNotFoundException $e) {
			throw new MethodNotFoundException($e->getMessage());
		}
	}


	/**
	 * @throws IJsonRPCAwareException
	 */
	protected function validateCommand(ICommand $command, ValidFormatRequest $request): void
	{
		try {
			$this->schemeValidator->validate($request->getMethod(), $request->getParams());
		} catch (SchemaValidatorException $e) {
			throw new InvalidParamsException($e->getMessage(), 0, $e);
		} catch (MissingSchemaException $e) {
			throw new ServerErrorException($e->getMessage(), 0, $e);
		}
	}


	/**
	 * @throws IJsonRPCAwareException
	 */
	protected function createCommandDTO(ICommand $command, ValidFormatRequest $request): ICommandDTO
	{
		/**
		 * Get command dto class name
		 */
		$commandDTOClass = $command->getCommandDTOClass();

		if (!class_exists($commandDTOClass)) {
			throw new ServerErrorException("Class $commandDTOClass does not exist");
		}

		if (!in_array(ICommandDTO::class, class_implements($commandDTOClass), true)) {
			throw new ServerErrorException("$commandDTOClass does not implement " . ICommandDTO::class);
		}

		try {
			return $commandDTOClass::fromValidParams($request->getParams());
		} catch (\InvalidArgumentException $e) {
			throw new InvalidParamsException($e->getMessage(), 0, $e);
		}
	}
}
