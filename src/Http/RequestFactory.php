<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Http;

use Contributte\JsonRPC\Exception\MissingHttpMethod;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

/**
 * @phpcs:disable SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
 */
final class RequestFactory
{

	public function createHttpRequest(): RequestInterface
	{
		return new Request(
			$this->getHttpMethod(),
			$this->getUri(),
			$this->getHttpHeaders(),
			$this->getRawBody()
		);
	}


	public function getRawBody(): string
	{
		return (string) file_get_contents('php://input');
	}


	private function getUri(): string
	{
		return $_SERVER['REQUEST_URI'] ?? '/';
	}


	private function getHttpMethod(): string
	{
		$method = $_SERVER['REQUEST_METHOD'] ?? null;

		if ($method === null) {
			throw new MissingHttpMethod('Please call api by HTTP agent');
		}

		if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
			$matched = preg_match('#^[A-Z]+\z#', $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);

			if ($matched === 1) {
				$method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
			}
		}

		return $method;
	}


	/**
	 * @return array|string[]
	 */
	private function getHttpHeaders(): array
	{
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();

			if ($headers === false) {
				$headers = [];
			}
		} else {
			$headers = [];

			foreach ($_SERVER as $key => $value) {
				if (strncmp($key, 'HTTP_', 5) === 0) {
					$key = substr($key, 5);
				} elseif (strncmp($key, 'CONTENT_', 8) !== 0) {
					continue;
				}

				$headers[str_replace('_', '-', $key)] = $value;
			}
		}

		return $headers;
	}
}
