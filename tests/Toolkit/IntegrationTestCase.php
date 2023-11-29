<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Tests\Toolkit;

use Contributte\Tester\Environment;
use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Predis\Client;
use Tester\TestCase;
use Throwable;

abstract class IntegrationTestCase extends TestCase
{

	private ?Container $container = null;


	protected function getContainer(): Container
	{
		if ($this->container === null) {
			$this->container = $this->createContainer();
		}

		return $this->container;
	}


	protected function setUp(): void
	{
		parent::setUp();

		try {
			$client = $this->getRedisClient();
			$client->ping();
			$client->select(6);
		} catch (Throwable $e) {
			Environment::skip('Redis is not available.');
		}
	}


	protected function tearDown(): void
	{
		parent::setUp();

		$client = $this->getRedisClient();
		$client->flushdb();
	}


	protected function getRedisClient(): Client
	{
		/** @var Client $client */
		$client = $this->getContainer()->getByType(Client::class);

		return $client;
	}


	private function createContainer(): Container
	{
		$rootDir = __DIR__ . '/../..';

		$config = new Configurator();
		$localConfigFile = __DIR__ . '/../config/config.local.neon';

		// share compiled container between tests
		$config->setTempDirectory(Environment::getTestDir());

		$config->addStaticParameters([
			'rootDir' => $rootDir,
		]);

		$config->addConfig(__DIR__ . '/../Fixtures/config.test.neon');

		if (file_exists($localConfigFile)) {
			$config->addConfig($localConfigFile);
		}

		$config->addStaticParameters([
			'wwwDir' => $rootDir . '/www',
			'appDir' => $rootDir . '/src',
		]);

		$container = $config->createContainer();

		return $this->container = $container;
	}
}
