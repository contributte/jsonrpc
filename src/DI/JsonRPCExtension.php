<?php declare(strict_types = 1);

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
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @method stdClass getConfig()
 */
final class JsonRPCExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'methodsMapping' => Expect::arrayOf(Expect::string(), Expect::string()),
			'jsonSchemaFilesDir' => Expect::string(),
			'projectName' => Expect::string(),
			'ttlInSeconds' => Expect::int()->default(31556926),
			'registerRedisPool' => Expect::bool()->default(true),
		]);
	}

	public function loadConfiguration(): void
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('httpRequestFactory'))
			->setType(RequestFactory::class);

		$builder->addDefinition($this->prefix('httpRequestCollectionFactory'))
			->setType(RequestCollectionFactory::class);

		$builder->addDefinition($this->prefix('localAdapterForSchemes'))
			->setFactory(Local::class, [$config->jsonSchemaFilesDir]);

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

		if (!is_int($config->ttlInSeconds)) {
			throw new \UnexpectedValueException('ttlInSeconds has to be int');
		}

		foreach ($config->methodsMapping as $commandName => $commandClass) {
			if ($builder->getByType($commandClass) === null) {
				$builder->addDefinition($this->prefix(sprintf('command.%s', $commandName)))
					->setType($commandClass)
					->addTag(InjectExtension::TagInject);
			}

			$commandProvider->addSetup('addCommandClass', [$commandName, $commandClass]);
		}
	}

	public function beforeCompile(): void
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$schemaProviderArgs = [
			$config->projectName,
			'@' . $this->prefix('schemaFileSystem'),
		];

		if ($config->registerRedisPool === true) {
			$builder->addDefinition($this->prefix('redisPool'))
				->setType(RedisPool::class)
				->setArguments([$config->ttlInSeconds]);

			$schemaProviderArgs[] = '@' . $this->prefix('redisPool');
		}

		$builder->addDefinition($this->prefix('schemaProvider'))
			->setType(SchemaProvider::class)
			->setArguments($schemaProviderArgs);
	}

}
