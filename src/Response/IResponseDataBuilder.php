<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Response;

use Gamee\JsonRPC\Request\RequestCollection;

interface IResponseDataBuilder
{

	public function buildResponseBadge(RequestCollection $requestCollection): array;


	/**
	 * @return array|mixed[]
	 */
	public function buildServerError(): array;


	/**
	 * @return array|mixed[]
	 */
	public function buildErrorResponse(
		int $code,
		string $message,
		string $reason,
		?string $id = null
	): array;


	/**
	 * @return array|mixed[]
	 */
	public function buildParseError(string $errorMessage): array;
}
