<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\JsonContentTrait;

/**
 * Shorthand for a JSON request body with a schema ref or type.
 *
 * @Annotation
 */
class JsonRequestBody extends OA\RequestBody
{
    use JsonContentTrait;

    public function __construct(array $properties)
    {
        $ref = $properties['ref'] ?? Generator::UNDEFINED;
        $type = $properties['type'] ?? Generator::UNDEFINED;
        unset($properties['ref'], $properties['type']);

        $resolved = $this->resolveSource($ref, Generator::isDefault($type) ? null : $type);

        if ($resolved['ref'] !== null || $resolved['type'] !== null) {
            $jsonContent = new OA\JsonContent(array_filter($resolved));
            $properties['value'] = array_merge($properties['value'] ?? [], [$jsonContent]);
        }

        parent::__construct($properties);
    }
}