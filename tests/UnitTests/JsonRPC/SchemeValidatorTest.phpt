<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\UnitTests\JsonRPC;

require_once __DIR__ . '/../../bootstrap.php';

use Gamee\JsonRPC\Exception\SchemaValidatorException;
use Gamee\JsonRPC\ISchemaProvider;
use Gamee\JsonRPC\SchemeValidator;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

/**
 * @testCase
 */
class SchemeValidatorTest extends TestCase
{

	public function testValidateParameters(): void
	{
		$json = Json::decode(file_get_contents(__DIR__ . '/input/validParameters.json'));

		$schemaProvider = $this->mockSchemaProvider();

		$schemeValidator = new SchemeValidator($schemaProvider);

		Assert::noError(function () use ($schemeValidator, $json): void {
			$schemeValidator->validate('schema', $json);
		});
	}


	/**
	 * @dataProvider dataProviderForInvalidParameters
	 */
	public function testThrowExceptionOnInvalidParameters(string $filePath, string $expectedMessage): void
	{
		$json = Json::decode(file_get_contents($filePath));

		$schemaProvider = $this->mockSchemaProvider();

		$schemeValidator = new SchemeValidator($schemaProvider);

		Assert::exception(function () use ($schemeValidator, $json): void {
			$schemeValidator->validate('schema', $json);
		}, SchemaValidatorException::class, $expectedMessage);
	}


	/**
	 * @return array|string[][]
	 */
	public function dataProviderForInvalidParameters(): array
	{
		return[
			[
				__DIR__ . '/input/invalidArray.json',
				'Parameters are not valid: arrayParameter : Object value found, but an array is required',
			],
			[
				__DIR__ . '/input/invalidEnum.json',
				'Parameters are not valid: enumParameter : Does not have a value in the enumeration ["option1","option2","option3"]',
			],
			[
				__DIR__ . '/input/invalidInt.json',
				'Parameters are not valid: intParameter : Double value found, but an integer is required',
			],
			[
				__DIR__ . '/input/invalidNull.json',
				'Parameters are not valid: nullParameter : Integer value found, but a string or a null is required',
			],
			[
				__DIR__ . '/input/invalidObject.json',
				'Parameters are not valid: objectParameter : Array value found, but an object is required',
			],
			[
				__DIR__ . '/input/invalidString.json',
				'Parameters are not valid: stringParameter : Integer value found, but a string is required',
			],
		];
	}


	/**
	 * @return MockInterface|ISchemaProvider
	 */
	private function mockSchemaProvider(): ISchemaProvider
	{
		$schema = file_get_contents(__DIR__ . '/input/validationSchema.json');
		$schemaClass = Json::decode($schema);

		return Mockery::mock(ISchemaProvider::class)
			->shouldReceive('getSchema')->andReturn($schemaClass)->getMock()
		;
	}
}

(new SchemeValidatorTest())->run();
