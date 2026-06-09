<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\JsonResponseTrait;

/**
 * Shorthand for a JSON response with a schema ref or type.
 *
 * @Annotation
 */
class JsonResponse extends OA\Response
{
    use JsonResponseTrait;

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
