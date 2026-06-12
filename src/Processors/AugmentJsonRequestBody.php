<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Attributes as OAXT;

class AugmentJsonRequestBody
{
    public function __invoke(Analysis $analysis): void
    {
        $requestBodies = $analysis->getAnnotationsOfType([OAX\JsonRequestBody::class, OAXT\JsonRequestBody::class]);

        foreach ($requestBodies as $requestBody) {
            if (!Generator::isDefault($requestBody->description)) {
                continue;
            }

            if (!Generator::isDefault($requestBody->source)) {
                $requestBody->description = $this->resolveDescription($requestBody->source, $analysis);
            }
        }
    }

    protected function resolveDescription(string $source, Analysis $analysis): string
    {
        $schema = $analysis->getAnnotationForSource($source, OA\Schema::class);

        if ($schema instanceof OA\Schema) {
            if (!Generator::isDefault($schema->title)) {
                return $schema->title;
            }
            if (!Generator::isDefault($schema->description)) {
                return $schema->description;
            }
            if (!Generator::isDefault($schema->schema)) {
                return $schema->schema;
            }
        }

        $pos = strrpos($source, '\\');

        return $pos !== false ? substr($source, $pos + 1) : $source;
    }
}
