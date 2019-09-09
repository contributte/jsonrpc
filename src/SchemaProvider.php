<?php

declare(strict_types=1);

namespace Gamee\JsonRPC;

use Gamee\JsonRPC\Cache\Key\JsonSchemaMemberKey;
use Gamee\JsonRPC\Cache\SchemaCacheItem;
use Gamee\JsonRPC\Exception\MissingSchemaException;
use Gamee\JsonRPC\Exception\SchemaValidatorException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Cache\CacheItemPoolInterface;

final class SchemaProvider implements ISchemaProvider
{

	/**
	 * @var Filesystem
	 */
	private $schemaFileSystem;

	/**
	 * @var string
	 */
	private $projectName;

	/**
	 * @var CacheItemPoolInterface
	 */
	private $cacheItemPool;


	public function __construct(
		string $projectName,
		Filesystem $schemaFileSystem,
		CacheItemPoolInterface $cacheItemPool
	) {
		$this->projectName = $projectName;
		$this->schemaFileSystem = $schemaFileSystem;
		$this->cacheItemPool = $cacheItemPool;
	}


	/**
	 * @throws SchemaValidatorException
	 * @throws MissingSchemaException
	 */
	public function getSchema(string $identifier): \stdClass
	{
		$key = new JsonSchemaMemberKey($this->projectName, $identifier);

		if ($this->cacheItemPool->hasItem($key->getMemberKey())) {
			return $this->parseJsonSchema($this->cacheItemPool->getItem($key->getMemberKey())->get(), $identifier);
		}

		$schema = null;

		try {
			$schemaFilePath = $this->getSchemaFilePath($identifier);
			$schema = $this->schemaFileSystem->read($schemaFilePath);
		} catch (FileNotFoundException $e) {
			throw $this->createMissingSchemaException($identifier);
		}

		if (!is_string($schema)) {
			throw $this->createMissingSchemaException($identifier);
		}

		$item = new SchemaCacheItem($key, $schema, new \DateTimeImmutable(), true, true);
		$this->cacheItemPool->save($item);

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


	private function parseJsonSchema(string $jsonSchema, string $identifier): \stdClass
	{
		try {
			return Json::decode($jsonSchema);
		} catch (JsonException $e) {
			throw new SchemaValidatorException(sprintf(
				'Schema for project %s and endpoint %s contains invalid JSON',
				$this->projectName,
				$identifier
			));
		}
	}


	private function createMissingSchemaException(string $identifier): MissingSchemaException
	{
		return new MissingSchemaException(sprintf(
			'Schema for project %s and endpoint %s does not exists',
			$this->projectName,
			$identifier
		));
	}
}
