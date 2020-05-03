[![Latest Stable Version](https://poser.pugx.org/contributte/jsonrpc/v/stable)](https://packagist.org/packages/contributte/jsonrpc)
[![License](https://poser.pugx.org/contributte/jsonrpc/license)](https://packagist.org/packages/contributte/jsonrpc)
[![Total Downloads](https://poser.pugx.org/contributte/jsonrpc/downloads)](https://packagist.org/packages/contributte/jsonrpc)
[![Build Status](https://travis-ci.org/contributte/jsonrpc.svg?branch=master)](https://travis-ci.org/contributte/jsonrpc)

# jsonrpc
JSON-RPC toolset build on top of psr-7 (guzzlehttp), league/json-guard, league/json-reference and league/flysystem

Common classes used for JSON-RPC APIs.

## Installation

### Composer:

```json
composer require contributte/jsonrpc
```

## Configuration

### config.neon

```
extensions:
	jsonRPCExtension: Contributte\JsonRPC\DI\JsonRPCExtension

jsonRPCExtension:
	methodsMapping:
		user.get: App\Command\Type\User\GetUserCommand
		user.resetPassword: App\Command\Type\User\ResetPasswordCommand
		# ...
	jsonSchemaFilesDir: %appDir%/../json-schema
```
