<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations as OAX;

#[\Attribute(\Attribute::TARGET_ALL | \Attribute::IS_REPEATABLE)]
class Middleware extends OAX\Middleware
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
            'value' => $this->combine($attachables),
        ]);
    }
}
