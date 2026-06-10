<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\ProvidesCustomizersInterface;

class MiddlewareCustomizers
{
    public function __invoke(Analysis $analysis): void
    {
        foreach ($analysis->getAnnotationsOfType(OA\Operation::class) as $operation) {
            $middleware = $this->resolveMiddleware($operation);
            if (!$middleware instanceof OAX\Middleware) {
                continue;
            }

            if (Generator::isDefault($middleware->attachables)) {
                continue;
            }

            foreach ($middleware->attachables as $attachable) {
                if (!($attachable instanceof ProvidesCustomizersInterface)) {
                    continue;
                }

                foreach ($attachable::customizers() as $annotationClass => $callables) {
                    if (!($operation instanceof $annotationClass)) {
                        continue;
                    }
                    foreach ($callables as $callable) {
                        $callable($operation);
                    }
                }
            }
        }
    }

    protected function resolveMiddleware(OA\Operation $operation): ?OAX\Middleware
    {
        if (Generator::isDefault($operation->attachables)) {
            return null;
        }

        foreach ($operation->attachables as $attachable) {
            if ($attachable instanceof OAX\Middleware) {
                return $attachable;
            }
        }

        return null;
    }
}
