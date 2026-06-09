# openapi-extras

[![Build Status](https://github.com/DerManoMann/openapi-extras/workflows/build/badge.svg)](https://github.com/DerManoMann/openapi-extras/actions)
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
                 ->clearUnusedComponents(enabled: true)
                 ->operationIdHashing(enabled: false)
                 ->pathsToMatch(['/api'])
                 ->enumDescription()
                 ->build(new NullLogger());
```

### `Controller`

The controller annotation may be used to:
* add an optional url prefix to all operations in the class
* add one or more `Tag`s to all operations in the class
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

#### Inheritance

Controller annotations are inherited from parent classes. This allows defining shared configuration on a base controller:

* **Prefixes** concatenate: parent `/api/v2` + child `/users` = `/api/v2/users`
* **Tags** merge (deduplicated)
* **Responses** merge by response code (child overrides parent for same code)
* **Headers** merge by header name (child overrides parent for same name)
* **Middlewares** merge by exact name (deduplicated)

Set `inherit: false` on a child controller to opt out of inheritance.

```php
<?php declare(strict_types=1);

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller(prefix: '/api/v2', tags: ['api'])]
#[OAT\Response(response: 403, description: 'Not allowed')]
#[OAX\Middleware([AuthMiddleware::class])]
abstract class BaseController
{
}

#[OAX\Controller(prefix: '/users', tags: ['users'])]
#[OAT\Response(response: 404, description: 'Not found')]
class UserController extends BaseController
{
    #[OAT\Get(path: '/list', operationId: 'listUsers')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function list(): mixed
    {
        // effective path: /api/v2/users/list
        // effective tags: ['api', 'users']
        // effective responses: 200, 403 (from parent), 404 (from child)
        return 'list';
    }
}
```

### `DataSchema`

The `DataSchema` annotation/attribute wraps properties inside a `data` object envelope, reducing boilerplate for APIs that use a standard wrapper pattern.

Properties with `nullable: false` are automatically added to the `data` object's `required` list.
You can also explicitly pass a `required` list in the constructor.

```php
<?php declare(strict_types=1);

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\DataSchema(schema: 'UserResource')]
class UserResource
{
    #[OAT\Property(property: 'id', type: 'integer', nullable: false)]
    public int $id;

    #[OAT\Property(property: 'name', type: 'string', nullable: false)]
    public string $name;

    #[OAT\Property(property: 'email', type: 'string')]
    public string $email;
}
```

This generates a schema equivalent to:

```yaml
UserResource:
  required:
    - data
  properties:
    data:
      required:
        - id
        - name
      properties:
        id:
          type: integer
          nullable: false
        name:
          type: string
          nullable: false
        email:
          type: string
      type: object
  type: object
```

### `Middleware`

`Middleware` annotations allow to attach a list of middleware names either individually or across all operations (via the `Controller` annotation).
Controller-level middlewares are merged onto all operations in the class; operation-level middlewares are additive.

This is used by the [openapi-router](https://github.com/DerManoMann/openapi-router) project to configure routing middleware from OpenAPI specs.

```php
<?php declare(strict_types=1);

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller(
    middlewares: [new OAX\Middleware([FooMiddleware::class])]
)]
class MiddlewareController
{
    #[OAT\Get(path: '/mw', operationId: 'mw')]
    #[OAT\Response(response: 200, description: 'All good')]
    #[OAX\Middleware([BarMiddleware::class])]
    public function mw()
    {
        return 'mw';
    }
}
```

### `JsonResponse`

A shorthand for JSON responses that reference a schema. Reduces nesting by wrapping the ref/type in a `JsonContent` automatically.

If no `description` is provided, it is derived from the referenced schema (fallback order: title > description > schema name > class short name).

```php
<?php declare(strict_types=1);

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAT\Schema(schema: 'TokenPairResource', title: 'Token pair')]
class TokenPairResource
{
    #[OAT\Property(property: 'access_token', type: 'string')]
    public string $accessToken;

    #[OAT\Property(property: 'refresh_token', type: 'string')]
    public string $refreshToken;
}

class AuthController
{
    #[OAT\Post(path: '/auth/login', operationId: 'login')]
    #[OAX\JsonResponse(response: 200, ref: TokenPairResource::class)]
    #[OAX\JsonResponse(response: 401, description: 'Invalid credentials')]
    public function login(): mixed
    {
        // response 200 description auto-derived as "Token pair" from schema title
        return '...';
    }
}
```

This is equivalent to the more verbose:

```php
#[OAT\Response(
    response: 200,
    description: 'Token pair',
    content: new OAT\JsonContent(ref: TokenPairResource::class)
)]
```


## License

The openapi-extras project is released under the [MIT license](LICENSE).
