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
                $this->applyChain($operation, $chain);
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
    protected function applyChain(OA\Operation $operation, array $chain): void
    {
        $mergedPrefix = '';
        $mergedTags = [];
        $mergedResponses = [];
        $mergedHeaders = [];
        $mergedMiddlewareNames = [];

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

            if (!Generator::isDefault($controller->attachables)) {
                foreach ($controller->attachables as $attachable) {
                    if ($attachable instanceof OAX\Middleware && $attachable->names) {
                        foreach ($attachable->names as $name) {
                            $mergedMiddlewareNames[$name] = $name;
                        }
                    }
                }
            }
        }

        // Also collect operation-level middlewares (they take highest precedence)
        if (!Generator::isDefault($operation->attachables)) {
            foreach ($operation->attachables as $attachable) {
                if ($attachable instanceof OAX\Middleware && $attachable->names) {
                    foreach ($attachable->names as $name) {
                        $mergedMiddlewareNames[$name] = $name;
                    }
                }
            }
        }

        $this->applyPrefix($operation, $mergedPrefix);
        $this->applyTags($operation, $mergedTags);
        $this->applyResponses($operation, $mergedResponses);
        $this->applyHeaders($operation, $mergedHeaders);
        $this->applyMiddlewares($operation, $mergedMiddlewareNames);
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
    protected function applyResponses(OA\Operation $operation, array $mergedResponses): void
    {
        if ($mergedResponses !== []) {
            $operation->merge(array_values($mergedResponses), true);
        }
    }

    /**
     * @param array<string, OA\Header> $mergedHeaders
     */
    protected function applyHeaders(OA\Operation $operation, array $mergedHeaders): void
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
     * @param array<string, string> $mergedMiddlewareNames
     */
    protected function applyMiddlewares(OA\Operation $operation, array $mergedMiddlewareNames): void
    {
        if ($mergedMiddlewareNames === []) {
            return;
        }

        // Remove existing operation-level middlewares (they've been merged into the resolved set)
        if (!Generator::isDefault($operation->attachables)) {
            $remaining = array_values(array_filter(
                $operation->attachables,
                fn ($a) => !($a instanceof OAX\Middleware)
            ));
            $operation->attachables = $remaining ?: Generator::UNDEFINED;
        }

        $middleware = new OAX\Middleware(['names' => array_values($mergedMiddlewareNames)]);
        $operation->merge([$middleware]);
    }

    protected function clearMerged(Analysis $analysis, $annotations): void
    {
        if (Generator::isDefault($annotations) || !$annotations) {
            return;
        }

        $annotations = is_array($annotations) ? $annotations : [$annotations];

        foreach ($annotations as $annotation) {
            $analysis->annotations->offsetUnset($annotation);
        }
    }
}
