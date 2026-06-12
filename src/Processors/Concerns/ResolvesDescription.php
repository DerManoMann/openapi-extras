<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors\Concerns;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

trait ResolvesDescription
{
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
