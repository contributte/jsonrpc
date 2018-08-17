<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Cache\Key;

final class JsonSchemaMemberKey extends JsonSchemaKey
{

	/**
	 * @var string
	 */
	private $endpoint;


	public function __construct(string $project, string $endpoint)
	{
		parent::__construct($project);

		$this->endpoint = $endpoint;
	}


	public function getMemberKey(): string
	{
		return sprintf('%s:%s', $this->projectName, $this->endpoint);
	}
}
