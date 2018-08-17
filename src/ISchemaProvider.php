<?php

declare(strict_types=1);

namespace Gamee\JsonRPC;

use Gamee\JsonRPC\Exception\MissingSchemaException;
use Gamee\JsonRPC\Exception\SchemaValidatorException;

interface ISchemaProvider
{

	/**
	 * @throws SchemaValidatorException
	 * @throws MissingSchemaException
	 */
	public function getSchema(string $identifier): \stdClass;
}
