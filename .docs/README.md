# Contributte JSON-RPC

JSON-RPC 2.0 toolset for [Nette Framework](https://nette.org/) built on top of PSR-7 ([guzzlehttp](https://github.com/guzzle/guzzle)), [justinrainbow/json-schema](https://github.com/justinrainbow/json-schema), [league/flysystem](https://github.com/thephpleague/flysystem) and [predis/predis](https://github.com/predis/predis).

## Content

- [Installation](#installation)
- [Configuration](#configuration)
  - [Minimal configuration](#minimal-configuration)
  - [Full configuration](#full-configuration)
  - [Caching](#caching)
- [Commands](#commands)
  - [ICommand](#icommand)
  - [ICommandDTO](#icommanddto)
- [JSON Schema validation](#json-schema-validation)
- [Request and response format](#request-and-response-format)
  - [Request format](#request-format)
  - [Success response](#success-response)
  - [Error response](#error-response)
  - [Batch requests](#batch-requests)
- [Error codes](#error-codes)
- [Examples](#examples)

## Installation

Install package using composer.

```bash
composer require contributte/jsonrpc
```

Register prepared [compiler extension](https://doc.nette.org/en/dependency-injection/nette-container) in your `config.neon` file.

```neon
extensions:
    jsonrpc: Contributte\JsonRPC\DI\JsonRPCExtension
```

> [!NOTE]
> For Redis caching support, install predis: `composer require predis/predis`

## Configuration

### Minimal configuration

```neon
extensions:
    jsonrpc: Contributte\JsonRPC\DI\JsonRPCExtension

jsonrpc:
    methodsMapping:
        user.get: App\Command\User\GetUserCommand
        user.create: App\Command\User\CreateUserCommand
    jsonSchemaFilesDir: %appDir%/../json-schema
```

### Full configuration

Here is the list of all available options with their types.

```neon
jsonrpc:
    methodsMapping: array<string, class-string>  # Required: Maps method names to command classes
    jsonSchemaFilesDir: <string>                 # Required: Directory containing JSON schema files
    projectName: <string>                        # Optional: Project identifier for cache keys
    ttlInSeconds: <int>                          # Optional: Cache TTL in seconds (default: 31556926 = 1 year)
    registerRedisPool: <bool>                    # Optional: Auto-register Redis cache pool (default: true)
```

For example:

```neon
jsonrpc:
    methodsMapping:
        user.get: App\Command\User\GetUserCommand
        user.create: App\Command\User\CreateUserCommand
        user.update: App\Command\User\UpdateUserCommand
        user.delete: App\Command\User\DeleteUserCommand
        feed.getAll: App\Command\Feed\GetAllFeedCommand
    jsonSchemaFilesDir: %appDir%/../json-schema
    projectName: my-api
    ttlInSeconds: 86400
    registerRedisPool: true
```

### Caching

JSON schemas are cached using Redis via a PSR-6 compatible cache pool. The cache is enabled by default when `registerRedisPool` is `true`.

> [!TIP]
> Cache significantly improves performance by avoiding repeated filesystem reads and JSON parsing of schema files.

To disable Redis caching:

```neon
jsonrpc:
    registerRedisPool: false
```

> [!WARNING]
> When caching is disabled, schemas will be read from disk on every request, which may impact performance.

## Commands

Commands implement the business logic for each JSON-RPC method. Each command consists of two parts: `ICommand` and `ICommandDTO`.

### ICommand

The `ICommand` interface defines a method handler.

```php
<?php declare(strict_types=1);

namespace App\Command\User;

use App\Command\User\GetUserCommandDTO;
use App\Repository\UserRepository;
use Contributte\JsonRPC\Command\ICommand;
use Contributte\JsonRPC\Command\ICommandDTO;
use Contributte\JsonRPC\Response\Enum\GenericCodes;
use Contributte\JsonRPC\Response\IResponse;
use Contributte\JsonRPC\Response\Type\ErrorResponse;
use Contributte\JsonRPC\Response\Type\SuccessResponse;

final class GetUserCommand implements ICommand
{

    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function getCommandDTOClass(): string
    {
        return GetUserCommandDTO::class;
    }

    public function execute(ICommandDTO $commandDTO): IResponse
    {
        assert($commandDTO instanceof GetUserCommandDTO);

        $user = $this->userRepository->find($commandDTO->getUserId());

        if ($user === null) {
            return new ErrorResponse(
                GenericCodes::CODE_INVALID_PARAMS,
                'Invalid params',
                'User not found',
            );
        }

        return new SuccessResponse((object) [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ]);
    }

}
```

### ICommandDTO

The `ICommandDTO` interface defines a Data Transfer Object that holds validated request parameters.

```php
<?php declare(strict_types=1);

namespace App\Command\User;

use Contributte\JsonRPC\Command\ICommandDTO;

final class GetUserCommandDTO implements ICommandDTO
{

    public function __construct(
        private int $userId,
    ) {
    }

    public static function fromValidParams(\stdClass $parameters): ICommandDTO
    {
        return new self((int) $parameters->userId);
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

}
```

> [!IMPORTANT]
> The `fromValidParams` method receives parameters that have already been validated against the JSON schema. Additional validation can be performed here if needed.

## JSON Schema validation

Each method requires a corresponding JSON schema file for parameter validation. Schema files must be named `{method-name}.json` and placed in the `jsonSchemaFilesDir` directory.

For method `user.get`, create file `user.get.json`:

```json
{
    "$schema": "http://json-schema.org/draft-04/schema#",
    "type": "object",
    "properties": {
        "userId": {
            "type": "integer",
            "description": "The user ID to retrieve"
        }
    },
    "required": ["userId"]
}
```

For method `feed.getAll`, create file `feed.getAll.json`:

```json
{
    "$schema": "http://json-schema.org/draft-04/schema#",
    "type": "object",
    "properties": {
        "pagination": {
            "type": "object",
            "properties": {
                "offset": {
                    "type": "integer",
                    "minimum": 0
                },
                "limit": {
                    "type": "integer",
                    "minimum": 1,
                    "maximum": 100
                }
            },
            "required": ["offset", "limit"]
        }
    },
    "required": ["pagination"]
}
```

> [!TIP]
> Take a look at more information about JSON Schema:
> - https://json-schema.org/
> - https://json-schema.org/understanding-json-schema/

## Request and response format

This library implements the [JSON-RPC 2.0 specification](https://www.jsonrpc.org/specification).

### Request format

```json
{
    "jsonrpc": "2.0",
    "method": "user.get",
    "params": {
        "userId": 123
    },
    "id": "unique-request-id"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `jsonrpc` | string | Yes | Must be `"2.0"` |
| `method` | string | Yes | Method name (e.g., `user.get`) |
| `params` | object | Yes | Method parameters |
| `id` | string\|null | No | Request identifier for matching responses |

### Success response

```json
{
    "jsonrpc": "2.0",
    "result": {
        "id": 123,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "id": "unique-request-id",
    "time": "2025-01-15T14:30:00+00:00"
}
```

### Error response

```json
{
    "jsonrpc": "2.0",
    "error": {
        "code": -32602,
        "message": "Invalid params",
        "data": {
            "reason": "User not found"
        }
    },
    "id": "unique-request-id",
    "time": "2025-01-15T14:30:00+00:00"
}
```

### Batch requests

The library supports batch requests as per JSON-RPC 2.0 specification:

**Request:**

```json
[
    {
        "jsonrpc": "2.0",
        "method": "user.get",
        "params": {"userId": 1},
        "id": "1"
    },
    {
        "jsonrpc": "2.0",
        "method": "user.get",
        "params": {"userId": 2},
        "id": "2"
    }
]
```

**Response:**

```json
[
    {
        "jsonrpc": "2.0",
        "result": {"id": 1, "name": "Alice"},
        "id": "1",
        "time": "2025-01-15T14:30:00+00:00"
    },
    {
        "jsonrpc": "2.0",
        "result": {"id": 2, "name": "Bob"},
        "id": "2",
        "time": "2025-01-15T14:30:00+00:00"
    }
]
```

## Error codes

The library uses standard JSON-RPC 2.0 error codes:

| Code | Constant | Message | Description |
|------|----------|---------|-------------|
| `-32700` | `CODE_PARSE_ERROR` | Parse error | Invalid JSON was received |
| `-32600` | `CODE_INVALID_REQUEST` | Invalid Request | The JSON sent is not a valid Request object |
| `-32601` | `CODE_METHOD_NOT_FOUND` | Method not found | The method does not exist / is not available |
| `-32602` | `CODE_INVALID_PARAMS` | Invalid params | Invalid method parameter(s) |
| `-32603` | `CODE_INTERNAL_ERROR` | Internal error | Internal JSON-RPC error |

These codes are defined in `Contributte\JsonRPC\Response\Enum\GenericCodes`.

> [!NOTE]
> You can also define custom error codes for your application-specific errors. The specification reserves codes from `-32000` to `-32099` for implementation-defined server errors.

## Examples

### Basic presenter implementation

```php
<?php declare(strict_types=1);

namespace App\Presenters;

use Contributte\JsonRPC\Request\IRequestProcessor;
use Contributte\JsonRPC\Request\RequestCollection;
use Contributte\JsonRPC\Request\RequestCollectionFactory;
use Contributte\JsonRPC\Request\Type\ValidFormatRequest;
use Contributte\JsonRPC\Response\IResponseDataBuilder;
use Nette\Application\Response;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;

final class ApiPresenter extends Presenter
{

    public function __construct(
        private RequestCollectionFactory $requestCollectionFactory,
        private IRequestProcessor $requestProcessor,
        private IResponseDataBuilder $responseDataBuilder,
    ) {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        $rawBody = $this->getHttpRequest()->getRawBody();

        if ($rawBody === null) {
            $this->sendJson(
                $this->responseDataBuilder->buildParseError('Empty request body'),
            );
        }

        try {
            $requestCollection = $this->requestCollectionFactory->create($rawBody);
        } catch (\Throwable $e) {
            $this->sendJson(
                $this->responseDataBuilder->buildParseError($e->getMessage()),
            );
        }

        $this->processRequests($requestCollection);

        $this->sendJson(
            $this->responseDataBuilder->buildResponseBadge($requestCollection),
        );
    }

    private function processRequests(RequestCollection $requestCollection): void
    {
        foreach ($requestCollection as $request) {
            if ($request instanceof ValidFormatRequest) {
                try {
                    $response = $this->requestProcessor->process($request);
                } catch (\Throwable $e) {
                    $response = \Contributte\JsonRPC\Response\Type\ErrorResponse::fromJsonRPCAwareException($e);
                }

                $requestCollection[$request] = $response;
            }
        }
    }

}
```

### Project structure

```
app/
├── Command/
│   ├── User/
│   │   ├── GetUserCommand.php
│   │   ├── GetUserCommandDTO.php
│   │   ├── CreateUserCommand.php
│   │   └── CreateUserCommandDTO.php
│   └── Feed/
│       ├── GetAllFeedCommand.php
│       └── GetAllFeedCommandDTO.php
├── Presenters/
│   └── ApiPresenter.php
└── config/
    └── config.neon

json-schema/
├── user.get.json
├── user.create.json
└── feed.getAll.json
```

> [!TIP]
> Take a look at more examples in [contributte/jsonrpc](https://github.com/contributte/jsonrpc) repository.
