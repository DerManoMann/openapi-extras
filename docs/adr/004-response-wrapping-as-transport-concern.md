# ADR-004: Response Wrapping as a Transport Concern

## Status

Accepted

## Context

APIs commonly wrap resource payloads in an envelope (e.g. `{"data": ...}`). Many frameworks follow this convention — for example Laravel's `JsonResource` defaults to `$wrap = 'data'` — but the pattern is framework-agnostic.

The question is where this wrapping should be expressed in the OpenAPI spec: on the schema definition (data shape) or on the response annotation (transport layer).

## Decision

Wrapping belongs on the response, not the schema.

`JsonResponse` accepts a `wrap` parameter (default `'data'`) that tells the `WrapJsonResponseContent` processor to wrap the resolved schema inside an inline envelope with the wrap key as a required property.

Schemas remain pure data shapes — they describe the resource, not how it's delivered.

## Consequences

- A single schema can be referenced by responses with different envelope conventions (or no envelope at all via a regular `OAT\Response`).
- The processor generates the wrapper inline per-response, so there's no shared "envelope" schema polluting components.
- If additional top-level properties are needed alongside the wrap key (pagination, links), the processor can be extended without changing schema definitions.
