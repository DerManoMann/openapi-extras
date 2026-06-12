<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Attributes as OAT;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\JsonContentTrait;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class JsonRequestBody extends OAT\RequestBody
{
    use JsonContentTrait;

    /**
     * @param string|class-string      $ref
     * @param string|class-string|null $type
     * @param array<string,mixed>|null $x
     * @param OAT\Attachable[]|null    $attachables
     */
    public function __construct(
        string|object $ref = Generator::UNDEFINED,
        string|null $type = null,
        ?string $request = null,
        ?string $description = null,
        ?bool $required = true,
        ?array $x = null,
        ?array $attachables = null,
    ) {
        $resolved = $this->resolveSource($ref, $type);

        $jsonContent = ($resolved['ref'] !== null || $resolved['type'] !== null)
            ? new OAT\JsonContent(ref: $resolved['ref'], type: $resolved['type'])
            : null;

        parent::__construct(
            request: $request,
            description: $description ?? Generator::UNDEFINED,
            required: $required,
            content: $jsonContent,
            x: $x,
            attachables: $attachables,
        );
    }
}
