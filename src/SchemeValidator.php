<?php

declare(strict_types=1);

namespace Gamee\JsonRPC;

use Gamee\JsonRPC\Exception\SchemaValidatorException;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Validator;

class SchemeValidator
{

	/**
	 * @var ISchemaProvider
	 */
	private $schemaProvider;


	public function __construct(ISchemaProvider $schemaProvider)
	{
		$this->schemaProvider = $schemaProvider;
	}


	/**
	 * @throws SchemaValidatorException
	 * @param array|mixed[]|\stdClass $parameters
	 */
	public function validate(string $identifier, $parameters): void
	{
		$schema = $this->schemaProvider->getSchema($identifier);

		$validator = new Validator($parameters, $schema);

		if ($validator->fails()) {
			$errors = array_map(function (ValidationError $error): string {
				return sprintf('%s : %s', $error->getDataPath(), $error->getMessage());
			}, $validator->errors());

			throw new SchemaValidatorException(
				sprintf('Parameters are not valid: %s', implode(', ', $errors))
			);
		}
	}
}
