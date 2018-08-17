<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Request;

interface IRequest
{

	public function getId(): ?string;
}
