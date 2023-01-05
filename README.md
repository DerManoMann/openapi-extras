# openapi-extras

[![Build Status](https://github.com/DerManoMann/openapi-extras/workflows/build/badge.svg?branch=main)](https://github.com/DerManoMann/openapi-extras/actions)
[![Coverage Status](https://coveralls.io/repos/github/DerManoMann/openapi-extras/badge.svg)](https://coveralls.io/github/DerManoMann/openapi-extras)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Introduction
Re-useable annotations for [swagger-php](https://github.com/zircote/swagger-php).

## Installation

You can use **composer** or simply **download the release**.

**Composer**

The preferred method is via [composer](https://getcomposer.org). Follow the
[installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.

Once composer is installed, execute the following command in your project root to install this library:

```sh
composer require radebatz/openapi-extras
```

## Registering the library

Use of the included annotations/attributes requires registration of a custom processor.
Also, in the case of annotations the registration of the custom alias / namespace used.

### Register library for attriutes

```php
<?php

use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;

$generator = new Generator();

// ...

$generator->addProcessor(new MergeControllerDefaults(), BuildPaths::class);

// ...
```

### Register library for annotations

```php
<?php

use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;

$generator = new Generator();

// ...

$namespace = 'Radebatz\\OpenApi\\Extras\\Annotations';
$generator
    ->addNamespace($namespace . '\\')
    ->addAlias('oax', $namespace),
    ->addProcessor(new MergeControllerDefaults(), BuildPaths::class);

// ...
```

## Basic usage

### Controller

The controller annotation may be used on class level to add an optional prefix to all
operations of that controller class.

Also, it can be used to share responses and headers with all endpoints.

Example adding the `/foo` prefix and a `403` response to all operations in the `MyController` class.

```php
<?php declare(strict_types=1);

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller(prefix: '/foo')]
#[OAT\Response(response: 403, description: 'Not allowed')]
class PrefixedController
{
    #[OAT\Get(path: '/prefixed', operationId: 'prefixed')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function prefixed(): mixed
    {
        return 'prefixed';
    }
}
```

### Middleware

The `Middleware` annotation is currently not used but will be used by a future version
of the [openappi-router](https://github.com/DerManoMann/openapi-router) project.

## License

The openapi-extras project is released under the [MIT license](LICENSE).
