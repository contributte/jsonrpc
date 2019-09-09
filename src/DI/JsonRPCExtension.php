<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\DI;

use Gamee\JsonRPC\Cache\RedisPool;
use Gamee\JsonRPC\Command\CommandProvider;
use Gamee\JsonRPC\Http\RequestFactory;
use Gamee\JsonRPC\Request\RequestCollectionFactory;
use Gamee\JsonRPC\Request\RequestProcessor;
use Gamee\JsonRPC\Response\ResponseDataBuilder;
use Gamee\JsonRPC\SchemaProvider;
use Gamee\JsonRPC\SchemeValidator;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\JsonReference\Dereferencer;
use Nette\DI\CompilerExtension;

/**
 * @property-read array $config
 */
final class JsonRPCExtension extends CompilerExtension
{

	/**
	 * @var array<array|string|int|bool>
	 */
	public $defaults = [
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

		$builder->addDefinition($this->prefix('dereferencer'))
			->setType(Dereferencer::class);

		if (!is_int($this->config['ttlInSeconds'])) {
			throw new \UnexpectedValueException('ttlInSeconds has to be int');
		}

		foreach ($this->config['methodsMapping'] as $commandName => $commandClass) {
			if ($builder->getByType($commandClass) === null) {
				$builder->addDefinition($this->prefix("command.$commandName"))
					->setType($commandClass);
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
