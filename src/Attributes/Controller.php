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
     * @param string[]|null            $tags
     * @param OAT\Header[]|null        $headers
     * @param OAT\Response[]|null      $responses
     * @param Middleware[]|null         $middlewares
     * @param array<string,mixed>|null $x
     * @param OAT\Attachable[]|null    $attachables
     */
    public function __construct(
        ?string $prefix = null,
        ?array $tags = null,
        ?array $headers = null,
        ?array $responses = null,
        ?array $middlewares = null,
        bool $inherit = true,
        // annotation
        ?array $x = null,
        ?array $attachables = null
    ) {
        parent::__construct([
            'prefix' => $prefix ?? Generator::UNDEFINED,
            'tags' => $tags,
            'inherit' => $inherit,
            'x' => $x ?? Generator::UNDEFINED,
            'value' => $this->combine($headers, $responses, $middlewares, $attachables),
        ]);
    }
}
