<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Attributes as OAT;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Controller extends \Radebatz\OpenApi\Extras\Annotations\Controller
{
    /**
     * @param OAT\Header[]             $headers
     * @param OAT\Response[]|null      $responses
     * @param Middleware[]|null        $middlewares
     * @param array<string,mixed>|null $x
     * @param OAT\Attachable[]|null    $attachables
     */
    public function __construct(
        ?string $prefix = null,
        ?array $headers = null,
        ?array $responses = null,
        ?array $middlewares = null,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    ) {
        parent::__construct([
            'prefix' => $prefix ?? Generator::UNDEFINED,
            'x' => $x ?? Generator::UNDEFINED,
            'value' => $this->combine($headers, $responses, $middlewares, $attachables),
        ]);
    }
}
