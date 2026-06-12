<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Attributes as OAXT;
use Radebatz\OpenApi\Extras\Processors\Concerns\ResolvesDescription;

class AugmentJsonRequestBody
{
    use ResolvesDescription;

    public function __invoke(Analysis $analysis): void
    {
        $requestBodies = $analysis->getAnnotationsOfType([OAX\JsonRequestBody::class, OAXT\JsonRequestBody::class]);

        foreach ($requestBodies as $requestBody) {
            $source = $this->resolveSource($requestBody);
            if ($source === null) {
                continue;
            }

            $requestBody->source = $source;

            if (Generator::isDefault($requestBody->content) && Generator::isDefault($requestBody->ref)) {
                $this->dispatch($requestBody, $source, $analysis);
            }

            if (Generator::isDefault($requestBody->description)) {
                $requestBody->description = $this->resolveDescription($source, $analysis);
            }
        }
    }

    protected function resolveSource(OAX\JsonRequestBody|OAXT\JsonRequestBody $requestBody): ?string
    {
        if (!Generator::isDefault($requestBody->source)) {
            return $requestBody->source;
        }

        $reflector = $requestBody->_context->reflector ?? null;
        if ($reflector instanceof \ReflectionParameter) {
            $type = $reflector->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                return $type->getName();
            }
        }

        return null;
    }

    protected function dispatch(OA\RequestBody $requestBody, string $source, Analysis $analysis): void
    {
        $sourceRequestBody = $analysis->getAnnotationForSource($source, OA\RequestBody::class);
        if ($sourceRequestBody instanceof OA\AbstractAnnotation) {
            $requestBody->ref = OA\Components::ref($sourceRequestBody);

            return;
        }

        $context = new Context(['nested' => $requestBody], $requestBody->_context);
        $jsonContent = new OA\JsonContent(['ref' => $source, '_context' => $context]);

        $analysis->addAnnotation($jsonContent, $context);
    }
}
