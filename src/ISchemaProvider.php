<?php

declare(strict_types=1);

namespace Contributte\JsonRPC;

use Contributte\JsonRPC\Exception\MissingSchemaException;
use Contributte\JsonRPC\Exception\SchemaValidatorException;

interface ISchemaProvider
{

	/**
	 * @throws SchemaValidatorException
	 * @throws MissingSchemaException
	 */
	public function getSchema(string $identifier): \stdClass;
}
