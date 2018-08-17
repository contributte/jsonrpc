# php-jsonrpc
JSON-RPC toolset build on top of psr-7 (guzzlehttp), league/json-guard, league/json-reference and league/flysystem

Common classes used for JSON-RPC APIs.

## Installation

### Composer:

```json
composer require gamee-backend/php-jsonrpc
```

## Configuration

### config.neon

```
extensions:
	jsonRPCExtension: Gamee\JsonRPC\DI\JsonRPCExtension

jsonRPCExtension:
	methodsMapping:
		user.get: App\Command\Type\User\GetUserCommand
		user.resetPassword: App\Command\Type\User\ResetPasswordCommand
		# ...
	jsonSchemaFilesDir: %appDir%/../json-schema
```
