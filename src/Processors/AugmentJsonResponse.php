<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations\JsonResponse;

class AugmentJsonResponse
{
    public function __invoke(Analysis $analysis): void
    {
        /** @var JsonResponse[] $responses */
        $responses = $analysis->getAnnotationsOfType(JsonResponse::class);

        foreach ($responses as $response) {
            if (!Generator::isDefault($response->description)) {
                continue;
            }

            $source = Generator::isDefault($response->ref) ? $response->type : $response->ref;
            if ($source && !Generator::isDefault($source)) {
                $response->description = $this->resolveDescription($source, $analysis);
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

        // Fallback to short class name
        $pos = strrpos($source, '\\');

        return $pos !== false ? substr($source, $pos + 1) : $source;
    }
}
