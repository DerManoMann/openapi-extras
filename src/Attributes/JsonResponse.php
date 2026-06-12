<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Attributes as OAT;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\JsonContentTrait;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class JsonResponse extends OAT\Response
{
    use JsonContentTrait;

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
        $this->resolveSource($ref, $type);

        parent::__construct(
            response: $response !== Generator::UNDEFINED ? $response : null,
            description: $description ?? Generator::UNDEFINED,
            headers: $headers,
            x: $x,
            attachables: $attachables,
        );
    }
}
