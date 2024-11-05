<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_ALL | \Attribute::IS_REPEATABLE)]
class Middleware extends \Radebatz\OpenApi\Extras\Annotations\Middleware
{
    /**
     * @param array<string|class-string>|null $names
     */
    public function __construct(
        ?array $names = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    ) {
        parent::__construct([
            'names' => $names ?? Generator::UNDEFINED,
            'x' => $x ?? Generator::UNDEFINED,
            'attachables' => $attachables ?? Generator::UNDEFINED,
            'value' => $this->combine($attachables),
        ]);
    }
}
