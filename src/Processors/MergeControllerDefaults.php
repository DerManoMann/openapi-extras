<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations\Controller;

/**
 * Update operation path if controller prefix given.
 */
class MergeControllerDefaults
{
    public function __invoke(Analysis $analysis)
    {
        /** @var Controller[] $controllers */
        $controllers = $analysis->getAnnotationsOfType(Controller::class);
        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        foreach ($controllers as $controller) {
            if ($this->needsProcessing($controller)) {
                foreach ($operations as $operation) {
                    if ($this->contextMatch($operation->_context, $controller->_context)) {
                        // update path
                        if ($controller->prefix) {
                            $path = $controller->prefix . '/' . $operation->path;
                            $operation->path = str_replace('//', '/', $path);
                        }

                        if ($controller->responses) {
                            $operation->merge($controller->responses, true);
                        }
                    }
                }
            }
        }

        foreach ($controllers as $controller) {
            $this->clearMerged($analysis, $controller);
            $this->clearMerged($analysis, $controller->responses);
        }
    }

    protected function needsProcessing(Controller $controller): bool
    {
        return !$controller->prefix
            || !$controller->responses
            || !Generator::isDefault($controller->attachables);
    }

    protected function contextMatch(?Context $context1, ?Context $context2): bool
    {
        return $context1 && $context2
            && $context1->namespace === $context2->namespace
            && $context1->class == $context2->class;
    }

    protected function clearMerged(Analysis $analysis, $annotations): void
    {
        if (Generator::isDefault($annotations)) {
            return;
        }

        $annotations = is_array($annotations) ? $annotations : [$annotations];

        foreach ($annotations as $annotation) {
            $analysis->annotations->detach($annotation);
        }
    }
}
