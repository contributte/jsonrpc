<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Tests\Utils;

use Nette\Configurator;
use Nette\DI\Container;
use Predis\Client;
use Tester\TestCase;

abstract class IntegrationTestCase extends TestCase
{

	/**
	 * @var Container
	 */
	private $container;


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

		$client = $this->getRedisClient();
		$client->select(6);
	}


	protected function tearDown(): void
	{
		parent::setUp();

		$client = $this->getRedisClient();
		$client->flushdb();
	}


	private function createContainer(): Container
	{
		$rootDir = __DIR__ . '/../..';

		$config = new Configurator;
		$localConfigFile = __DIR__ . '/../config/config.local.neon';

		// share compiled container between tests
		$config->setTempDirectory(__DIR__ . '/../temp');

		$config->addParameters([
			'rootDir' => $rootDir,
		]);

		$config->addConfig(__DIR__ . '/../config/config.test.neon');

		if (file_exists($localConfigFile)) {
			$config->addConfig($localConfigFile);
		}

		$config->addParameters([
			'wwwDir' => $rootDir . '/www',
			'appDir' => $rootDir . '/src',
		]);

		$container = $config->createContainer();

		return $this->container = $container;
	}


	protected function getRedisClient(): Client
	{
		/** @var Client $client */
		$client = $this->getContainer()->getByType(Client::class);

		return $client;
	}
}
