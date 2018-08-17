<?php

declare(strict_types=1);

namespace Gamee\JsonRPC\Cache\Exception;

use Psr\Cache\InvalidArgumentException;

final class InvalidKeyException extends \InvalidArgumentException implements InvalidArgumentException
{

}
