<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations as OAX;

/**
 * Update operation path if controller prefix given.
 */
class MergeControllerDefaults
{
    public function __invoke(Analysis $analysis)
    {
        /** @var OAX\Controller[] $controllers */
        $controllers = $analysis->getAnnotationsOfType(OAX\Controller::class);
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($controllers as $controller) {
            if ($this->needsProcessing($controller)) {
                foreach ($operations as $operation) {
                    if ($this->isContextMatch($operation->_context, $controller->_context)) {
                        $this->processPrefix($operation, $controller);
                        $this->processResponses($operation, $controller);
                        $this->processHeaders($operation, $controller);
                        $this->processMiddlewares($operation, $controller);
                    }
                }
            }
        }

        foreach ($controllers as $controller) {
            $this->clearMerged($analysis, $controller->headers);
            $this->clearMerged($analysis, $controller->responses);
            $this->clearMerged($analysis, $controller);
        }
    }

    protected function processPrefix(OA\Operation $operation, OAX\Controller $controller): void
    {
        if ($controller->prefix && !Generator::isDefault($controller->prefix)) {
            $path = $controller->prefix . '/' . $operation->path;
            $operation->path = str_replace('//', '/', $path);
        }
    }

    protected function processResponses(OA\Operation $operation, OAX\Controller $controller): void
    {
        if ($controller->responses) {
            $operation->merge($controller->responses, true);
        }
    }

    protected function processHeaders(OA\Operation $operation, OAX\Controller $controller): void
    {
        if ($controller->headers && !Generator::isDefault($operation->responses)) {
            foreach ($operation->responses as $response) {
                foreach ($controller->headers as $header) {
                    if (Generator::isDefault($response->headers) || !in_array($header, $response->headers, true)) {
                        // avoid duplicates (Attributes: shared headers are already merged into shared responses)
                        $response->merge($controller->headers, true);
                    }
                }
            }
        }
    }

    protected function processMiddlewares(OA\Operation $operation, OAX\Controller $controller): void
    {
        if (!Generator::isDefault($controller->attachables)) {
            $middlewares = [];
            foreach ($controller->attachables as $attachable) {
                if ($attachable instanceof OAX\Middleware) {
                    $middlewares[] = $attachable;
                }
            }
            $operation->merge($middlewares);
        }
    }

    protected function needsProcessing(OAX\Controller $controller): bool
    {
        return ($controller->prefix && !Generator::isDefault($controller->prefix))
            || $controller->headers
            || $controller->responses
            || !Generator::isDefault($controller->attachables);
    }

    protected function isContextMatch(?Context $context1, ?Context $context2): bool
    {
        return $context1 && $context2
            && $context1->namespace === $context2->namespace
            && $context1->class == $context2->class;
    }

    protected function clearMerged(Analysis $analysis, $annotations): void
    {
        if (Generator::isDefault($annotations) || !$annotations) {
            return;
        }

        $annotations = is_array($annotations) ? $annotations : [$annotations];

        foreach ($annotations as $annotation) {
            $analysis->annotations->detach($annotation);
        }
    }
}
