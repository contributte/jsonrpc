<?php declare(strict_types = 1);

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
		return $this->getRequestHeaderValue('REQUEST_URI') ?? '/';
	}

	private function getHttpMethod(): string
	{
		$method = $this->getRequestHeaderValue('REQUEST_METHOD');

		if ($method === null) {
			throw new MissingHttpMethod('Please call api by HTTP agent');
		}

		$xHttpMethodOverride = $this->getRequestHeaderValue('HTTP_X_HTTP_METHOD_OVERRIDE');
		if ($method === 'POST' && $xHttpMethodOverride !== null) {
			$matched = preg_match('#^[A-Z]+\z#', $xHttpMethodOverride);

			if ($matched === 1) {
				$method = $xHttpMethodOverride;
			}
		}

		return $method;
	}

	/**
	 * @return array<string,string>
	 */
	private function getHttpHeaders(): array
	{
		if (function_exists('apache_request_headers')) {
			/** @var array<string,string> $headers */
			$headers = apache_request_headers();
		} else {
			$headers = [];

			foreach ($_SERVER as $key => $value) {
				if (strncmp($key, 'HTTP_', 5) === 0) {
					$key = substr($key, 5);
				} elseif (strncmp($key, 'CONTENT_', 8) !== 0) {
					continue;
				}

				$headers[str_replace('_', '-', $key)] = $this->getRequestHeaderValue($key) ?? '';
			}
		}

		return $headers;
	}

	private function getRequestHeaderValue(string $headerName): ?string
	{
		if (array_key_exists($headerName, $_SERVER) && is_string($_SERVER[$headerName])) {
			return $_SERVER[$headerName];
		}

		return null;
	}

}
