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
    public $ref = Generator::UNDEFINED;

    /** @var string|class-string|null */
    public $type = Generator::UNDEFINED;

    public function __construct(array $properties)
    {
        $ref = $properties['ref'] ?? Generator::UNDEFINED;
        $type = $properties['type'] ?? Generator::UNDEFINED;

        $jsonContentProps = [];
        if (!Generator::isDefault($ref)) {
            $jsonContentProps['ref'] = $ref;
        }
        if (!Generator::isDefault($type)) {
            $jsonContentProps['type'] = $type;
        }

        if ($jsonContentProps !== []) {
            $jsonContent = new OA\JsonContent($jsonContentProps);
            $properties['value'] = array_merge($properties['value'] ?? [], [$jsonContent]);
        }

        parent::__construct($properties);
    }
}
