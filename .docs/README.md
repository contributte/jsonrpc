[![Latest Stable Version](https://poser.pugx.org/contributte/jsonrpc/v/stable)](https://packagist.org/packages/contributte/jsonrpc)
[![License](https://poser.pugx.org/contributte/jsonrpc/license)](https://packagist.org/packages/contributte/jsonrpc)
[![Total Downloads](https://poser.pugx.org/contributte/jsonrpc/downloads)](https://packagist.org/packages/contributte/jsonrpc)
[![Build Status](https://travis-ci.org/contributte/jsonrpc.svg?branch=master)](https://travis-ci.org/contributte/jsonrpc)

# JSON-RPC

JSON-RPC toolset build on top of psr-7 (guzzlehttp), league/json-guard, league/json-reference and league/flysystem

Common classes used for JSON-RPC APIs.

## Installation

```bash
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

## Maintainers

<table>
	<tbody>
		<tr>
			<td align="center">
				<a href="https://github.com/paveljanda">
						<img width="150" height="150" src="https://avatars0.githubusercontent.com/u/1488874?s=400&v=4">
				</a>
				</br>
				<a href="https://github.com/paveljanda">Pavel Janda</a>
			</td>
			<td align="center">
				<a href="https://github.com/gameeapp">
						<img width="150" height="150" src="https://avatars3.githubusercontent.com/u/13903740?s=200&v=4">
				</a>
				</br>
				<a href="https://github.com/gameeapp">Gameeapp</a>
			</td>
		</tr>
	</tbody>
</table>

-----

Thank you for testing, reporting and contributing.
