<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Cache;

use Contributte\JsonRPC\Cache\Exception\InvalidKeyException;
use Contributte\JsonRPC\Cache\Exception\NotImplementedException;
use Contributte\JsonRPC\Cache\Key\JsonSchemaMemberKey;
use Predis\Client;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class RedisPool implements CacheItemPoolInterface
{

	private int $ttlInSeconds;

	private Client $predisClient;

	/** @var array|CacheItemInterface[] */
	private array $deferrerItems = [];

	public function __construct(
		int $ttlInSeconds,
		Client $predisClient
	)
	{
		$this->ttlInSeconds = $ttlInSeconds;
		$this->predisClient = $predisClient;
	}

	public function getItem(string $key): CacheItemInterface
	{
		$redisKey = $this->createKey($key);

		if (!$this->hasItem($key)) {
			return new SchemaCacheItem($redisKey, '', new \DateTimeImmutable(), false, false);
		}

		$value = $this->predisClient->hget(
			$redisKey->getKeyString(),
			$redisKey->getMemberKey()
		);

		$expiration = (new \DateTimeImmutable())->add(
			new \DateInterval(sprintf('PT%sS', $this->ttlInSeconds))
		);

		return new SchemaCacheItem($redisKey, (string) $value, $expiration, true, true);
	}

	/**
	 * @param string[] $keys
	 * @return iterable<CacheItemInterface>
	 */
	public function getItems(array $keys = []): iterable
	{
		$items = [];

		foreach ($keys as $key) {
			$items[] = $this->getItem($key);
		}

		return $items;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasItem($key): bool
	{
		$redisKey = $this->createKey($key);

		return $this->predisClient->hexists(
			$redisKey->getKeyString(),
			$redisKey->getMemberKey()
		) === 1;
	}

	public function clear(): bool
	{
		throw new NotImplementedException();
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteItem($key): bool
	{
		$redisKey = $this->createKey($key);

		$this->predisClient->hdel($redisKey->getKeyString(), [$redisKey->getMemberKey()]);

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteItems(array $keys): bool
	{
		foreach ($keys as $key) {
			$this->deleteItem($key);
		}

		return true;
	}

	public function save(CacheItemInterface $item): bool
	{
		$redisKey = $this->createKey($item->getKey());

		$this->predisClient->hset(
			$redisKey->getKeyString(),
			$redisKey->getMemberKey(),
			(string) $item->get() // @phpstan-ignore-line
		);

		return true;
	}

	public function saveDeferred(CacheItemInterface $item): bool
	{
		$this->deferrerItems[] = $item;

		return true;
	}

	public function commit(): bool
	{
		foreach ($this->deferrerItems as $item) {
			$this->save($item);
		}

		return true;
	}

	private function createKey(string $key): JsonSchemaMemberKey
	{
		$matched = preg_match('/.+:.+/u', $key);

		if ($matched !== 1) {
			throw new InvalidKeyException('Key must be in format project:endpoint');
		}

		[$project, $endpoint] = explode(':', $key);

		return new JsonSchemaMemberKey($project, $endpoint);
	}

}
