<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\DI;

use Contributte\JsonRPC\Cache\RedisPool;
use Contributte\JsonRPC\Command\CommandProvider;
use Contributte\JsonRPC\Http\RequestFactory;
use Contributte\JsonRPC\Request\RequestCollectionFactory;
use Contributte\JsonRPC\Request\RequestProcessor;
use Contributte\JsonRPC\Response\ResponseDataBuilder;
use Contributte\JsonRPC\SchemaProvider;
use Contributte\JsonRPC\SchemeValidator;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Nette\DI\CompilerExtension;
use Nette\DI\Extensions\InjectExtension;

/**
 * @property-read array $config
 */
final class JsonRPCExtension extends CompilerExtension
{

	/**
	 * @var array<array|string|int|bool>
	 */
	private array $defaults = [
		'methodsMapping' => [], // commandName => Command\Class,
		'jsonSchemaFilesDir' => '',
		'projectName' => '',
		'ttlInSeconds' => 31556926, // 1 year
		'registerRedisPool' => true,
	];


	public function loadConfiguration(): void
	{
		$this->validateConfig($this->defaults);

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('httpRequestFactory'))
			->setType(RequestFactory::class);

		$builder->addDefinition($this->prefix('httpRequestCollectionFactory'))
			->setType(RequestCollectionFactory::class);

		$builder->addDefinition($this->prefix('localAdapterForSchemes'))
			->setFactory(Local::class, [$this->config['jsonSchemaFilesDir']]);

		$builder->addDefinition($this->prefix('schemaFileSystem'))
			->setType(Filesystem::class)->setAutowired(false);

		$builder->addDefinition($this->prefix('schemeValidator'))
			->setFactory(SchemeValidator::class);

		$builder->addDefinition($this->prefix('httpRequest'))
			->setFactory('@' . RequestFactory::class . '::createHttpRequest');

		$builder->addDefinition($this->prefix('responseDataBuilder'))
			->setType(ResponseDataBuilder::class);

		$builder->addDefinition($this->prefix('requestProcessor'))
			->setType(RequestProcessor::class);

		$commandProvider = $builder->addDefinition($this->prefix('commandProvider'))
			->setType(CommandProvider::class);

		if (!is_int($this->config['ttlInSeconds'])) {
			throw new \UnexpectedValueException('ttlInSeconds has to be int');
		}

		foreach ($this->config['methodsMapping'] as $commandName => $commandClass) {
			if ($builder->getByType($commandClass) === null) {
				$builder->addDefinition($this->prefix("command.$commandName"))
					->setType($commandClass)
					->addTag(InjectExtension::TAG_INJECT);
			}

			$commandProvider->addSetup('addCommandClass', [$commandName, $commandClass]);
		}
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		$schemaProviderArgs = [
			$this->config['projectName'],
			'@' . $this->prefix('schemaFileSystem'),
		];

		if ($this->config['registerRedisPool'] === true) {
			$builder->addDefinition($this->prefix('redisPool'))
				->setType(RedisPool::class)
				->setArguments([$this->config['ttlInSeconds']]);

			$schemaProviderArgs[] = '@' . $this->prefix('redisPool');
		}

		$builder->addDefinition($this->prefix('schemaProvider'))
			->setType(SchemaProvider::class)
			->setArguments($schemaProviderArgs);
	}
}
