# ADR-003: Middleware Provides Customizers

## Status

Accepted

## Context

Controllers often declare middleware that implies OpenAPI metadata. For example, a `jwt-auth` middleware means the operation requires bearer authentication — yet users must manually add `security: [['bearerAuth' => []]]` to every operation. This duplication is error-prone and violates DRY.

The library already has a `Customizers` processor for global callbacks, but those apply to all instances of an annotation type. What's needed is *scoped* customizers — OpenAPI modifications that only apply to operations carrying a specific middleware.

## Decision

### `ProvidesCustomizersInterface`

A new interface that any `Middleware` subclass can implement:

```php
interface ProvidesCustomizersInterface
{
    /** @return array<class-string<OA\AbstractAnnotation>, callable[]> */
    public static function customizers(): array;
}
```

The return format matches the existing `Customizers` processor's mapping shape — annotation class to list of callables.

### Custom middleware attributes

Users extend `OAX\Middleware` to create self-contained middleware annotations that carry both the middleware name(s) and the OpenAPI effect:

```php
#[\Attribute(\Attribute::TARGET_ALL | \Attribute::IS_REPEATABLE)]
class SecureMiddleware extends Middleware implements ProvidesCustomizersInterface
{
    public function __construct()
    {
        parent::__construct(names: ['jwt-auth']);
    }

    public static function customizers(): array
    {
        return [
            OA\Operation::class => [
                fn (OA\Operation $op) => $op->security = [['bearerAuth' => []]],
            ],
        ];
    }
}
```

Usage becomes `#[SecureMiddleware]` — no redundant security annotation needed.

### Preserving original instances through merge

`MergeControllerDefaults` already collapses all middleware into a single `Middleware` instance with merged names. To preserve typed instances for later processing, the original middleware objects are stored as nested attachables on the merged `Middleware`:

```php
$middleware = new OAX\Middleware([
    'names' => array_values($mergedMiddlewareNames),
    'value' => array_values($mergedMiddlewareInstances),
]);
```

This required adding `$_nested = [OA\Attachable::class => ['attachables']]` to the `Middleware` annotation.

### `MiddlewareCustomizers` processor

A single-pass processor that runs after `BuildPaths` (operations are fully resolved). It:

1. Iterates all operations
2. Finds the merged `Middleware` attachable
3. Checks its nested attachables for `ProvidesCustomizersInterface` implementors
4. Applies their customizers to the operation

Customizers are scoped — they only affect operations that carry the middleware, unlike global `Customizers` which apply to all instances.

## Consequences

- Middleware subclasses are the primary extension point. No registration step needed — implement the interface and use the attribute.
- The interface is not coupled to `Middleware` specifically. Any attachable could implement it in the future if a similar pattern emerges.
- String-based middleware names (e.g., `'jwt-auth'`) cannot provide customizers directly — only class-based middleware can. Users needing effects for string-only middleware should create a wrapping attribute class.
- The `Middleware` annotation now supports nested attachables, which is a backward-compatible addition.
- Pipeline ordering: `MiddlewareCustomizers` runs after `BuildPaths` but before `AugmentParameters`, so it sees fully assembled operations but can still influence parameter augmentation.
