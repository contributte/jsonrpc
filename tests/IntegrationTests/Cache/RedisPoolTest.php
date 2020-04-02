<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Tests\IntegrationTests\Cache;

require_once __DIR__ . '/../../bootstrap.php';

use Gamee\JsonRPC\Cache\Key\JsonSchemaMemberKey;
use Gamee\JsonRPC\Cache\RedisPool;
use Gamee\JsonRPC\Cache\SchemaCacheItem;
use Gamee\JsonRPC\Tests\Utils\IntegrationTestCase;
use Tester\Assert;

/**
 * @testCase
 */
class RedisPoolTest extends IntegrationTestCase
{

	private RedisPool $redisPool;


	public function testBasicFunctionality(): void
	{
		$key = new JsonSchemaMemberKey('test', 'test-endpoint');
		$item = new SchemaCacheItem($key, 'test-value', new \DateTimeImmutable(), true, true);

		$key2 = new JsonSchemaMemberKey('test', 'test-endpoint2');
		$item2 = new SchemaCacheItem($key2, 'test-value2', new \DateTimeImmutable(), true, true);
		$this->redisPool->save($item);

		Assert::true($this->redisPool->hasItem($key->getMemberKey()));

		$itemFromCache = $this->redisPool->getItem($key->getMemberKey());

		Assert::equal($item->get(), $itemFromCache->get());

		$this->redisPool->saveDeferred($item2);

		Assert::false($this->redisPool->hasItem($key2->getMemberKey()));

		$this->redisPool->commit();

		Assert::true($this->redisPool->hasItem($key2->getMemberKey()));

		$itemsFromCache = $this->redisPool->getItems([
			$key->getMemberKey(),
			$key2->getMemberKey(),
		]);

		if ($itemsFromCache instanceof \Traversable) {
			$itemsFromCache = iterator_to_array($itemsFromCache);
		}

		$valuesFromCache = array_map(function (SchemaCacheItem $item): string {
			return $item->get();
		}, $itemsFromCache);

		Assert::equal([
			$item->get(),
			$item2->get(),
		], $valuesFromCache);

		$this->redisPool->deleteItem($key->getMemberKey());

		Assert::false($this->redisPool->hasItem($key->getMemberKey()));

		$this->redisPool->deleteItems([$key2->getMemberKey()]);

		Assert::false($this->redisPool->hasItem($key2->getMemberKey()));

		$emptyItem = new SchemaCacheItem($key, '', new \DateTimeImmutable(), false, false);
		$emptyItemFromCache = $this->redisPool->getItem($key->getMemberKey());

		Assert::equal($emptyItem->get(), $emptyItemFromCache->get());
	}


	protected function setUp(): void
	{
		parent::setUp();

		/** @var RedisPool $redisPool */
		$redisPool = $this->getContainer()->getByType(RedisPool::class);

		$this->redisPool = $redisPool;
	}
}

(new RedisPoolTest())->run();
