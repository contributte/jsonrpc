<?php declare(strict_types = 1);

namespace Contributte\JsonRPC;

use Contributte\JsonRPC\Cache\Key\JsonSchemaMemberKey;
use Contributte\JsonRPC\Cache\SchemaCacheItem;
use Contributte\JsonRPC\Exception\MissingSchemaException;
use Contributte\JsonRPC\Exception\SchemaValidatorException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Cache\CacheItemPoolInterface;
use UnexpectedValueException;

final class SchemaProvider implements ISchemaProvider
{

	private Filesystem $schemaFileSystem;

	private string $projectName;

	private CacheItemPoolInterface $cacheItemPool;

	public function __construct(
		string $projectName,
		Filesystem $schemaFileSystem,
		CacheItemPoolInterface $cacheItemPool
	)
	{
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
			$item = $this->cacheItemPool->getItem($key->getMemberKey())->get();

			return $this->parseJsonSchema($item, $identifier);
		}

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

	private function parseJsonSchema(mixed $jsonSchema, string $identifier): \stdClass
	{
		if (!is_string($jsonSchema)) {
			throw new UnexpectedValueException(sprintf(
				'Schema for project %s and endpoint %s contains invalid JSON',
				$this->projectName,
				$identifier
			));
		}

		try {
			return (object) Json::decode($jsonSchema);
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
