<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Attributes as OAT;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
class JsonRequestBody extends OAT\RequestBody
{
    /** @var string|class-string */
    public string|object $source = Generator::UNDEFINED;

    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'source'];

    /**
     * @param string|class-string      $ref
     * @param array<string,mixed>|null $x
     * @param OAT\Attachable[]|null    $attachables
     */
    public function __construct(
        string|object $ref = Generator::UNDEFINED,
        ?string $request = null,
        ?string $description = null,
        ?bool $required = true,
        ?array $x = null,
        ?array $attachables = null,
    ) {
        $this->source = $ref;

        parent::__construct(
            request: $request,
            description: $description ?? Generator::UNDEFINED,
            required: $required,
            x: $x,
            attachables: $attachables,
        );
    }
}
