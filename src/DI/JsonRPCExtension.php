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

final class JsonRPCExtension extends CompilerExtension
{

	/**
	 * @var array<array|string|int>
	 */
	public $defaults = [
		'methodsMapping' => [], // commandName => Command\Class,
		'jsonSchemaFilesDir' => '',
		'projectName' => '',
		'ttlInSeconds' => 31556926, // 1 year
	];


	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->defaults, $this->getConfig());
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('httpRequestFactory'))
			->setType(RequestFactory::class);

		$builder->addDefinition($this->prefix('httpRequestCollectionFactory'))
			->setType(RequestCollectionFactory::class);

		$builder->addDefinition($this->prefix('localAdapterForSchemes'))
			->setFactory(Local::class, [$config['jsonSchemaFilesDir']]);

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

		if (!is_int($config['ttlInSeconds'])) {
			throw new \UnexpectedValueException('ttlInSeconds has to be int');
		}

		$builder->addDefinition($this->prefix('redisPool'))
			->setType(RedisPool::class)
			->setArguments([$config['ttlInSeconds']]);

		$builder->addDefinition($this->prefix('schemaProvider'))
			->setType(SchemaProvider::class)
			->setArguments([
				$config['projectName'],
				'@' . $this->prefix('redisPool'),
				'@' . $this->prefix('schemaFileSystem'),
			]);

		foreach ($config['methodsMapping'] as $commandName => $commandClass) {
			if ($builder->getByType($commandClass) === null) {
				$builder->addDefinition($this->prefix("command.$commandName"))
					->setType($commandClass);
			}

			$commandProvider->addSetup('addCommandClass', [$commandName, $commandClass]);
		}
	}
}
