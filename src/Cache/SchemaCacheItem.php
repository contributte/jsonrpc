<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Cache;

use Gamee\JsonRPC\Cache\Key\JsonSchemaMemberKey;
use Psr\Cache\CacheItemInterface;

final class SchemaCacheItem implements CacheItemInterface
{

	/**
	 * @var JsonSchemaMemberKey
	 */
	private $key;

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @var \DateTimeInterface
	 */
	private $expiresAt;

	/**
	 * @var bool
	 */
	private $isHit;

	/**
	 * @var bool
	 */
	private $exists;


	public function __construct(
		JsonSchemaMemberKey $key,
		string $value,
		\DateTimeInterface $expiresAt,
		bool $isHit = false,
		bool $exists = false
	)
	{
		$this->key = $key;
		$this->value = $value;
		$this->expiresAt = $expiresAt;
		$this->isHit = $isHit;
		$this->exists = $exists;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getKey(): string
	{
		return $this->key->getMemberKey();
	}


	/**
	 * {@inheritDoc}
	 */
	public function get()
	{
		return $this->value;
	}


	/**
	 * {@inheritDoc}
	 */
	public function isHit(): bool
	{
		return $this->isHit;
	}


	/**
	 * {@inheritDoc}
	 */
	public function set($value)
	{
		return new static($this->key, $value, $this->expiresAt, $this->isHit, $this->exists);
	}


	/**
	 * {@inheritDoc}
	 */
	public function expiresAt($expiration)
	{
		if ($expiration === null) {
			return new static($this->key, $this->value, new \DateTimeImmutable('2030-12-31'));
		}

		return new static($this->key, $this->value, $expiration);
	}


	/**
	 * {@inheritDoc}
	 */
	public function expiresAfter($time)
	{
		if ($time === null) {
			return new static($this->key, $this->value, new \DateTimeImmutable('2030-12-31'));
		}

		if (is_int($time)) {
			$time = new \DateInterval(sprintf('PT%sS', $time));
		}

		return new static($this->key, $this->value, (new \DateTimeImmutable())->add($time));
	}
}
