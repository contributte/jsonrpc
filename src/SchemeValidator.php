<?php

declare(strict_types=1);

namespace Contributte\JsonRPC;

use Contributte\JsonRPC\Exception\SchemaValidatorException;
use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Validator;

class SchemeValidator
{

	private ISchemaProvider $schemaProvider;


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

		$validator = new Validator;

		try {
			$validator->validate($parameters, $schema);
		} catch (InvalidArgumentException $e) {
			throw new SchemaValidatorException(
				sprintf('Parameters are not valid: %s', $e->getMessage())
			);
		}

		if ($validator->isValid()) {
			return;
		}

		$errors = array_map(function (array $error): string {
			return sprintf('%s : %s', $error['property'], $error['message']);
		}, $validator->getErrors());

		throw new SchemaValidatorException(
			sprintf('Parameters are not valid: %s', implode(', ', $errors))
		);
	}
}
