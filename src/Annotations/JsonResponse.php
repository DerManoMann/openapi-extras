<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Shorthand for a JSON response with a schema ref.
 *
 * @Annotation
 */
class JsonResponse extends OA\Response
{
    /** @var string|class-string */
    public string|object $source = Generator::UNDEFINED;

    public string $wrap = 'data';

    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'source', 'wrap'];

    public function __construct(array $properties)
    {
        $this->source = $properties['ref'] ?? Generator::UNDEFINED;
        $this->wrap = $properties['wrap'] ?? 'data';
        unset($properties['ref'], $properties['wrap']);

        parent::__construct($properties);
    }
}
