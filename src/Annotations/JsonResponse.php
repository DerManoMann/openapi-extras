<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Shorthand for a JSON response with a schema ref or type.
 *
 * @Annotation
 */
class JsonResponse extends OA\Response
{
    /** @var string|class-string */
    public $source = Generator::UNDEFINED;

    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'source'];

    public function __construct(array $properties)
    {
        $ref = $properties['ref'] ?? Generator::UNDEFINED;
        $type = $properties['type'] ?? Generator::UNDEFINED;
        unset($properties['ref'], $properties['type']);

        $jsonContentProps = [];
        if (!Generator::isDefault($ref)) {
            $jsonContentProps['ref'] = $ref;
            $properties['source'] = $ref;
        }
        if (!Generator::isDefault($type)) {
            $jsonContentProps['type'] = $type;
            $properties['source'] = $properties['source'] ?? $type;
        }

        if ($jsonContentProps !== []) {
            $jsonContent = new OA\JsonContent($jsonContentProps);
            $properties['value'] = array_merge($properties['value'] ?? [], [$jsonContent]);
        }

        parent::__construct($properties);
    }
}
