# ADR-002: Processor Pipeline Ordering

## Status

Accepted

## Context

The swagger-php `Generator` runs annotations through a pipeline of processors in a fixed order. This library adds three custom processors that must be inserted at specific positions relative to the default pipeline to function correctly.

## Decision

### `MergeControllerDefaults` — inserted before `BuildPaths`

This processor merges controller-level configuration (prefix, tags, responses, headers, middlewares) into operations. It must run **before** `BuildPaths` because:

- `BuildPaths` assembles operations into path items. If prefixes haven't been applied yet, paths will be grouped incorrectly.
- Tags must be on operations before `AugmentTags` generates the top-level tag list.
- Responses and headers must be merged before `AugmentParameters`/`AugmentRefs` resolve references.

### `EnumDescription` — inserted before `ExpandEnums`

This processor generates human-readable descriptions from PHP enum cases. It must run **before** `ExpandEnums` because:

- `ExpandEnums` replaces enum class references with their scalar values. After it runs, the link back to the `ReflectionEnum` (needed to enumerate case names) is lost.

### `Customizers` — appended at the end

This processor invokes user-defined callbacks on annotation instances. It runs **last** because:

- All standard processing and augmentation is complete, so customizers see the final state.
- Customizers may need to override or adjust values set by earlier processors.
- Running earlier would risk having customizations overwritten by subsequent processors.

## Consequences

- Adding a new processor requires considering where it fits relative to both swagger-php's default processors and the existing extras processors.
- The `insert(processor, AnchorClass)` API means positions are resilient to new processors being added to swagger-php's defaults (as long as the anchor class isn't removed).
- If swagger-php removes or renames `BuildPaths` or `ExpandEnums`, the `insert()` calls will throw `OpenApiException` at runtime — this will surface immediately in tests.