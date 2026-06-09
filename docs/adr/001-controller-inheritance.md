# ADR-001: Controller Annotation Inheritance

## Status

Accepted

## Context

Currently, `MergeControllerDefaults` only matches operations to controllers in the **same class** (exact namespace + class match). If a parent class has `#[OAX\Controller(prefix: '/api/v2')]` and a child class has `#[OAX\Controller(prefix: '/user')]`, the parent's controller is not applied to the child's operations.

This limits reuse — common prefixes, shared responses, headers, and middleware must be duplicated across controllers rather than declared once on a base class.

## Decision

Support class hierarchy inheritance for `Controller` annotations with the following rules:

### Prefix

Concatenate from most-distant ancestor to child: parent `/api/v2` + child `/user` = `/api/v2/user`. A child with no prefix inherits the parent prefix unchanged.

### Tags

Merge and deduplicate. Tags from the full controller chain are applied to all operations in the class.

### Responses

Merge by response code. Child response with same code overrides parent's.

### Headers

Merge by header name. Child header with same name overrides parent's.

### Middlewares

Flatten all middleware names from ancestor chain + child, dedupe by exact name. The result is a single merged `Middleware` instance per operation containing the unique names list.

### Opt-out

New `inherit` property (bool, default `true`) on `Controller`. Set to `false` to stop inheriting from parent controllers. The inheritance chain stops at any controller with `inherit: false`.

### Resolution order

Controllers are applied from most-distant ancestor first, then down to the direct class. This means child values take precedence (override or append to parent).

## Implementation

The processor uses `Analysis::getSuperClasses()` (from swagger-php) to walk the class hierarchy and `Context::fullyQualifiedName()` to resolve FQCNs for lookup.

## Consequences

- Existing behavior is unchanged (no parent classes = no inheritance applied).
- Controllers that explicitly set `inherit: false` are fully isolated.
- The feature requires classes to be resolvable via PHP reflection (standard swagger-php usage).
