<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\JsonContentTrait;

/**
 * Shorthand for a JSON response with a schema ref or type.
 *
 * @Annotation
 */
class JsonResponse extends OA\Response
{
    use JsonContentTrait;

    public function __construct(array $properties)
    {
        $ref = $properties['ref'] ?? Generator::UNDEFINED;
        $type = $properties['type'] ?? Generator::UNDEFINED;
        unset($properties['ref'], $properties['type']);

        $this->resolveSource($ref, Generator::isDefault($type) ? null : $type);

        parent::__construct($properties);
    }
}
