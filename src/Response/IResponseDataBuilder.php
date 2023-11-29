<?php declare(strict_types = 1);

namespace Contributte\JsonRPC\Response;

use Contributte\JsonRPC\Request\RequestCollection;

interface IResponseDataBuilder
{

	/**
	 * @return array<mixed>
	 */
	public function buildResponseBadge(RequestCollection $requestCollection): array;

	/**
	 * @return array<mixed>
	 */
	public function buildServerError(): array;

	/**
	 * @return array<mixed>
	 */
	public function buildErrorResponse(
		int $code,
		string $message,
		string $reason,
		?string $id = null
	): array;

	/**
	 * @return array<mixed>
	 */
	public function buildParseError(string $errorMessage): array;

}
