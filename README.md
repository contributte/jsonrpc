[![Latest Stable Version](https://poser.pugx.org/gamee/php-jsonrpc/v/stable)](https://packagist.org/packages/gamee/php-jsonrpc)
[![License](https://poser.pugx.org/gamee/php-jsonrpc/license)](https://packagist.org/packages/gamee/php-jsonrpc)
[![Total Downloads](https://poser.pugx.org/gamee/php-jsonrpc/downloads)](https://packagist.org/packages/gamee/php-jsonrpc)
[![Build Status](https://travis-ci.org/gameeapp/php-jsonrpc.svg?branch=master)](https://travis-ci.org/gameeapp/php-jsonrpc)

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
