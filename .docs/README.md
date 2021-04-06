# Contributte / JSON-RPC

JSON-RPC toolset build on top of psr-7 (guzzlehttp), league/json-guard, league/json-reference and league/flysystem

Common classes used for JSON-RPC APIs.

## Content

- [Installation](#installation)
- [Configuration](#configuration)
    + [config.neon](#configneon)

## Installation

```bash
composer require contributte/jsonrpc
```

## Configuration

### config.neon

```neon
extensions:
	jsonRPCExtension: Contributte\JsonRPC\DI\JsonRPCExtension

jsonRPCExtension:
	methodsMapping:
		user.get: App\Command\Type\User\GetUserCommand
		user.resetPassword: App\Command\Type\User\ResetPasswordCommand
		# ...
	jsonSchemaFilesDir: %appDir%/../json-schema
```
