<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Request;

use Gamee\JsonRPC\Command\CommandProvider;
use Gamee\JsonRPC\Command\Exception\CommandNotFindException;
use Gamee\JsonRPC\Command\ICommand;
use Gamee\JsonRPC\Command\ICommandDTO;
use Gamee\JsonRPC\Exception\MissingSchemaException;
use Gamee\JsonRPC\Exception\SchemaValidatorException;
use Gamee\JsonRPC\GenericException\IJsonRPCAwareException;
use Gamee\JsonRPC\GenericException\InvalidParamsException;
use Gamee\JsonRPC\GenericException\MethodNotFoundException;
use Gamee\JsonRPC\GenericException\ServerErrorException;
use Gamee\JsonRPC\Request\Type\ValidFormatRequest;
use Gamee\JsonRPC\Response\IResponse;
use Gamee\JsonRPC\SchemeValidator;

class RequestProcessor implements IRequestProcessor
{

	/**
	 * @var CommandProvider
	 */
	private $commandProvider;

	/**
	 * @var SchemeValidator
	 */
	private $schemeValidator;


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
		} catch (CommandNotFindException $e) {
			throw new MethodNotFoundException(
				"Method [{$request->getMethod()}] does not exist"
			);
		} catch (\InvalidArgumentException $e) {
			throw new ServerErrorException(
				"Method [{$request->getMethod()}] has invalid implementation"
			);
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
