<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations as OAX;

class MergeControllerDefaults
{
    public function __invoke(Analysis $analysis): void
    {
        $controllers = $analysis->getAnnotationsOfType(OAX\Controller::class);
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        $controllerMap = $this->buildControllerMap($controllers);

        foreach ($operations as $operation) {
            $chain = $this->resolveControllerChain($operation->_context, $controllerMap, $analysis);
            if ($chain !== []) {
                $this->applyChain($analysis, $operation, $chain);
            }
        }

        foreach ($controllers as $controller) {
            $this->clearMerged($analysis, $controller->headers);
            $this->clearMerged($analysis, $controller->responses);
            $this->clearMerged($analysis, $controller);
        }
    }

    /**
     * @param  OAX\Controller[]              $controllers
     * @return array<string, OAX\Controller>
     */
    protected function buildControllerMap(array $controllers): array
    {
        $map = [];
        foreach ($controllers as $controller) {
            $fqcn = $controller->_context->fullyQualifiedName($controller->_context->class);
            if ($fqcn) {
                $map[$fqcn] = $controller;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, OAX\Controller> $controllerMap
     * @return OAX\Controller[]
     */
    protected function resolveControllerChain(?Context $operationContext, array $controllerMap, Analysis $analysis): array
    {
        if (!$operationContext || !$operationContext->class) {
            return [];
        }

        $fqcn = $operationContext->fullyQualifiedName($operationContext->class);
        if (!$fqcn) {
            return [];
        }

        $chain = [];

        // Direct class controller
        $directController = $controllerMap[$fqcn] ?? null;
        if ($directController) {
            $chain[] = $directController;

            if (!$directController->inherit) {
                return $chain;
            }
        }

        // Walk up the inheritance chain
        $superClasses = $analysis->getSuperClasses($fqcn);
        foreach (array_keys($superClasses) as $parentFqcn) {
            $parentController = $controllerMap[$parentFqcn] ?? null;
            if ($parentController) {
                $chain[] = $parentController;

                if (!$parentController->inherit) {
                    break;
                }
            }
        }

        // Reverse so most-distant ancestor is first, direct class is last
        return array_reverse($chain);
    }

    /**
     * @param OAX\Controller[] $chain
     */
    protected function applyChain(Analysis $analysis, OA\Operation $operation, array $chain): void
    {
        $mergedPrefix = '';
        $mergedTags = [];
        $mergedResponses = [];
        $mergedHeaders = [];
        $mergedMiddlewares = [];

        foreach ($chain as $controller) {
            if ($controller->prefix && !Generator::isDefault($controller->prefix)) {
                $mergedPrefix .= '/' . trim($controller->prefix, '/');
            }

            if ($controller->tags) {
                foreach ($controller->tags as $tag) {
                    $mergedTags[$tag] = $tag;
                }
            }

            if ($controller->responses) {
                foreach ($controller->responses as $response) {
                    $mergedResponses[$response->response] = $response;
                }
            }

            if ($controller->headers) {
                foreach ($controller->headers as $header) {
                    $mergedHeaders[$header->header] = $header;
                }
            }

            $mergedMiddlewares = $this->collectMiddlewares($controller->attachables, $mergedMiddlewares);
        }

        $mergedMiddlewares = $this->collectMiddlewares($operation->attachables, $mergedMiddlewares);

        $this->applyPrefix($operation, $mergedPrefix);
        $this->applyTags($operation, $mergedTags);
        $this->applyResponses($analysis, $operation, $mergedResponses);
        $this->applyHeaders($analysis, $operation, $mergedHeaders);
        $this->applyMiddlewares($analysis, $operation, $mergedMiddlewares);
    }

    protected function applyPrefix(OA\Operation $operation, string $mergedPrefix): void
    {
        if ($mergedPrefix !== '') {
            $path = $mergedPrefix . '/' . ltrim($operation->path, '/');
            $operation->path = str_replace('//', '/', $path);
        }
    }

    /**
     * @param array<string, string> $mergedTags
     */
    protected function applyTags(OA\Operation $operation, array $mergedTags): void
    {
        if ($mergedTags === []) {
            return;
        }

        $operationTags = Generator::isDefault($operation->tags) ? [] : $operation->tags;
        $operation->tags = array_values(array_unique([...$mergedTags, ...$operationTags]));
    }

    /**
     * @param array<string|int, OA\Response> $mergedResponses
     */
    protected function applyResponses(Analysis $analysis, OA\Operation $operation, array $mergedResponses): void
    {
        if ($mergedResponses !== []) {
            $operation->merge(array_values($mergedResponses), true);
        }
    }

    /**
     * @param array<string, OA\Header> $mergedHeaders
     */
    protected function applyHeaders(Analysis $analysis, OA\Operation $operation, array $mergedHeaders): void
    {
        if ($mergedHeaders && !Generator::isDefault($operation->responses)) {
            foreach ($operation->responses as $response) {
                foreach ($mergedHeaders as $header) {
                    if (Generator::isDefault($response->headers) || !in_array($header, $response->headers, true)) {
                        $response->merge([$header], true);
                    }
                }
            }
        }
    }

    /**
     * @param array<string, OAX\Middleware> $mergedMiddlewares
     *
     * @return array<string, OAX\Middleware>
     */
    protected function collectMiddlewares(mixed $attachables, array $mergedMiddlewares = []): array
    {
        if (Generator::isDefault($attachables) || !is_array($attachables)) {
            return $mergedMiddlewares;
        }

        // Last occurrence wins: child controllers override ancestors, operation overrides all.
        foreach ($attachables as $attachable) {
            if ($attachable instanceof OAX\Middleware && $attachable->names) {
                foreach ($attachable->names as $name) {
                    $mergedMiddlewares[$name] = $attachable;
                }
            }
        }

        return $mergedMiddlewares;
    }

    /**
     * @param array<string, OAX\Middleware> $mergedMiddlewares
     */
    protected function applyMiddlewares(Analysis $analysis, OA\Operation $operation, array $mergedMiddlewares): void
    {
        if ($mergedMiddlewares === []) {
            return;
        }

        if (!Generator::isDefault($operation->attachables)) {
            $remaining = array_values(array_filter(
                $operation->attachables,
                fn ($a) => !($a instanceof OAX\Middleware)
            ));
            $operation->attachables = $remaining ?: Generator::UNDEFINED;
        }

        $operation->merge(array_values($mergedMiddlewares));
    }

    protected function clearMerged(Analysis $analysis, $annotations): void
    {
        if (Generator::isDefault($annotations) || !$annotations) {
            return;
        }

        $annotations = is_array($annotations) ? $annotations : [$annotations];

        foreach ($annotations as $annotation) {
            /* @phpstan-ignore function.alreadyNarrowedType */
            if (method_exists($analysis, 'removeAnnotation')) {
                $analysis->removeAnnotation($annotation);
            } else {
                $analysis->annotations->offsetUnset($annotation);
            }
        }
    }
}
