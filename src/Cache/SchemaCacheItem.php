<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Cache;

use Contributte\JsonRPC\Cache\Key\JsonSchemaMemberKey;
use Psr\Cache\CacheItemInterface;

final class SchemaCacheItem implements CacheItemInterface
{

	private JsonSchemaMemberKey $key;

	private mixed $value;

	private \DateTimeInterface $expiresAt;

	private bool $isHit;

	private bool $exists;

	public function __construct(
		JsonSchemaMemberKey $key,
		mixed $value,
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

	public function getKey(): string
	{
		return $this->key->getMemberKey();
	}

	public function get(): mixed
	{
		return $this->value;
	}

	public function isHit(): bool
	{
		return $this->isHit;
	}

	public function set(mixed $value): static
	{
		return new static($this->key, $value, $this->expiresAt, $this->isHit, $this->exists);
	}

	public function expiresAt(?\DateTimeInterface $expiration): static
	{
		if ($expiration === null) {
			return new static($this->key, $this->value, new \DateTimeImmutable('2030-12-31'));
		}

		return new static($this->key, $this->value, $expiration);
	}

	public function expiresAfter(int|\DateInterval|null $time): static
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
