<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Shorthand for a JSON request body with a schema ref.
 *
 * @Annotation
 */
class JsonRequestBody extends OA\RequestBody
{
    /** @var string|class-string */
    public string|object $source = Generator::UNDEFINED;

    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'source'];

    public function __construct(array $properties)
    {
        $this->source = $properties['ref'] ?? Generator::UNDEFINED;
        unset($properties['ref']);

        parent::__construct($properties);
    }
}
