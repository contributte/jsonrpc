<?php

declare(strict_types=1);

namespace Contributte\JsonRPC\Request;

interface IRequest
{

	public function getId(): ?string;
}
