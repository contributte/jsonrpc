<?php

declare(strict_types=1);

namespace Gamee\JsonRPC;

use Gamee\JsonRPC\Exception\SchemaValidatorException;
use JsonSchema\Validator;

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

		$validator = new Validator;

		$validator->validate($parameters, $schema);

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
