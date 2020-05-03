<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\GenericException;

interface IJsonRPCAwareException extends \Throwable
{

	// @codingStandardsIgnoreStart
	/**
	 * @return string
	 */
	public function getMessage();
	// @codingStandardsIgnoreEnds

	public function getErrorCode(): int;

	public function getGeneralMessage(): string;
}
