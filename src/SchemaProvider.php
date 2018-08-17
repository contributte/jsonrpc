<?php

declare(strict_types=1);

namespace Gamee\JsonRPC;

use Gamee\JsonRPC\Cache\Key\JsonSchemaMemberKey;
use Gamee\JsonRPC\Cache\RedisPool;
use Gamee\JsonRPC\Cache\SchemaCacheItem;
use Gamee\JsonRPC\Exception\MissingSchemaException;
use Gamee\JsonRPC\Exception\SchemaValidatorException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

final class SchemaProvider implements ISchemaProvider
{

	/**
	 * @var RedisPool
	 */
	private $redisPool;

	/**
	 * @var Filesystem
	 */
	private $schemaFileSystem;

	/**
	 * @var string
	 */
	private $projectName;


	public function __construct(string $projectName, RedisPool $redisPool, Filesystem $schemaFileSystem)
	{
		$this->redisPool = $redisPool;
		$this->schemaFileSystem = $schemaFileSystem;
		$this->projectName = $projectName;
	}


	/**
	 * @throws SchemaValidatorException
	 * @throws MissingSchemaException
	 */
	public function getSchema(string $identifier): \stdClass
	{
		$key = new JsonSchemaMemberKey($this->projectName, $identifier);

		if ($this->redisPool->hasItem($key->getMemberKey())) {
			return $this->parseJsonSchema($this->redisPool->getItem($key->getMemberKey())->get(), $identifier);
		}

		$schema = null;

		try {
			$schemaFilePath = $this->getSchemaFilePath($identifier);
			$schema = $this->schemaFileSystem->read($schemaFilePath);
		} catch (FileNotFoundException $e) {
			$this->throwMissingSchemaException($identifier);
		}

		if (!is_string($schema)) {
			$this->throwMissingSchemaException($identifier);
		}

		$item = new SchemaCacheItem($key, $schema, new \DateTimeImmutable(), true, true);
		$this->redisPool->save($item);

		return $this->parseJsonSchema($item->get(), $identifier);
	}


	private function getSchemaFilePath(string $identifier): string
	{
		$path = sprintf('%s.json', $identifier);

		if (!$this->schemaFileSystem->has($path)) {
			throw new MissingSchemaException(
				sprintf('Schema for request "%s" is missing, please add JSON schema in %s', $identifier, $path)
			);
		}

		return $path;
	}


	/**
	 * @throws SchemaValidatorException
	 */
	private function parseJsonSchema(string $jsonSchema, string $identifier): \stdClass
	{
		try {
			return Json::decode($jsonSchema);
		} catch (JsonException $e) {
			throw new SchemaValidatorException(sprintf(
				'Schema for project %s and endpoint %s does not exists',
				$this->projectName,
				$identifier
			));
		}
	}


	/**
	 * @throws MissingSchemaException
	 */
	private function throwMissingSchemaException(string $identifier): void
	{
		throw new MissingSchemaException(sprintf(
			'Schema for project %s and endpoint %s does not exists',
			$this->projectName,
			$identifier
		));
	}
}
