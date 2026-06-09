<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Attributes as OAT;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class JsonResponse extends \Radebatz\OpenApi\Extras\Annotations\JsonResponse
{
    /**
     * @param string|class-string      $ref
     * @param string|class-string|null $type
     * @param OAT\Header[]|null        $headers
     * @param array<string,mixed>|null $x
     * @param OAT\Attachable[]|null    $attachables
     */
    public function __construct(
        int|string $response = Generator::UNDEFINED,
        string|object $ref = Generator::UNDEFINED,
        string|null $type = null,
        ?string $description = null,
        ?array $headers = null,
        ?array $x = null,
        ?array $attachables = null,
    ) {
        parent::__construct([
            'ref' => $ref,
            'type' => $type ?? Generator::UNDEFINED,
            'response' => $response,
            'description' => $description ?? Generator::UNDEFINED,
            'x' => $x ?? Generator::UNDEFINED,
            'value' => $this->combine($headers, $attachables),
        ]);
    }
}
