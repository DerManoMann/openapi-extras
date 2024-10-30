# openapi-extras

[![Build Status](https://github.com/DerManoMann/openapi-extras/workflows/build/badge.svg?branch=main)](https://github.com/DerManoMann/openapi-extras/actions)
[![Coverage Status](https://coveralls.io/repos/github/DerManoMann/openapi-extras/badge.svg)](https://coveralls.io/github/DerManoMann/openapi-extras)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Introduction
Extra attributes/annotations and other bits for [swagger-php](https://github.com/zircote/swagger-php).

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

Use of the included annotations/attributes requires registration of a custom `swagger-php` processor.
Also, in the case of annotations, the registration of custom aliases / namespaces needs to be done manually.

### Using the Builder

When using the `OpenApiBuilder` no additional registration code is required as the builder will always
configure the required `MergeControllerDefaults` processor.

```php
<?php

use Radebatz\OpenApi\Extras\OpenApiBuilder;

$generator = (new OpenApiBuilder())->build();

// ...
```

### Register library for attributes

```php
<?php

use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;

$generator = new Generator();
$generator->getProcessorPipeline()
            ->insert(new MergeControllerDefaults(), BuildPaths::class);

// ...
```

### Register library for annotations

```php
<?php

use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;

$namespace = 'Radebatz\\OpenApi\\Extras\\Annotations';

$generator = new Generator();
$generator
    ->addNamespace($namespace . '\\')
    ->addAlias('oax', $namespace),
    ->getProcessorPipeline()
    ->insert(new MergeControllerDefaults(), BuildPaths::class);

// ...
```

## Basic usage

### `OpenApiBuilder`

The builder aims to simplify configuring the `swagger-php` `Generator` class by implementing
explicit methods to configure all default processors.
Futhermore, it also adds a new `Customizer` processor which allows to pre-process all instances
of a given OpenApi annotation/attribute by registering callbacks.

```php
<?php declare(strict_types=1);

use OpenApi\Annotations as OA;
use Psr\Log\NullLogger;
use Radebatz\OpenApi\Extras\OpenApiBuilder;

$generator = (new OpenApiBuilder())
                 ->addCustomizer(OA\Info::class, fn (OA\Info $info) => $info->description = 'Foo')
                 ->tagsToMatch(['admin'])
                 ->clearUnused(enabled: true)
                 ->operationIdHashing(enabled: false)
                 ->pathsToMatch(['/api'])
                 ->enumDescription()
                 ->build(new NullLogger());
```

### `Controller`

The controller annotation may be used to:
* add an optional url prefix to all operations in the class
* share one or more `Response`s across all operations
* share one or more `Header`'s across all operations
* share one or more `Middleware`'s across all operations

Example for adding the `/foo` prefix and a `403` response to all operations in the `MyController` class.

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

### `Middleware`

The `Middleware` annotation is currently not used but will be used by a future version
of the [openapi-router](https://github.com/DerManoMann/openapi-router) project.

`Middleware` annotations allow to share a list of middleware names either individually or across all operations (via the `Controller` annotation).

```php
<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller()]
#[OAX\Middleware([MyFooMiddleware::class])]
class MiddlewareController
{
    #[OAT\Get(path: '/mw', operationId: 'mw')]
    #[OAT\Response(response: 200, description: 'All good')]
    #[OAX\Middleware(['BarMiddleware'])]
    public function mw()
    {
        return 'mw';
    }
}
```


## License

The openapi-extras project is released under the [MIT license](LICENSE).
