<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Attributes as OAT;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class JsonResponse extends OAT\Response
{
    /** @var string|class-string */
    public string|object $source = Generator::UNDEFINED;

    public string $wrap = 'data';

    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'source', 'wrap'];

    /**
     * @param string|class-string                                                  $ref
     * @param OAT\MediaType[]|OAT\JsonContent|OAT\XmlContent|OAT\Attachable[]|null $content
     * @param OAT\Header[]|null                                                    $headers
     * @param OAT\Link[]|null                                                      $links
     * @param array<string,mixed>|null                                             $x
     * @param OAT\Attachable[]|null                                                $attachables
     */
    public function __construct(
        int|string $response = Generator::UNDEFINED,
        string|object $ref = Generator::UNDEFINED,
        string $wrap = 'data',
        ?string $description = null,
        array|OAT\JsonContent|OAT\XmlContent|null $content = null,
        ?array $headers = null,
        ?array $links = null,
        ?array $x = null,
        ?array $attachables = null,
    ) {
        $this->source = $ref;
        $this->wrap = $wrap;

        parent::__construct(
            response: $response !== Generator::UNDEFINED ? $response : null,
            description: $description ?? Generator::UNDEFINED,
            headers: $headers,
            content: $content,
            links: $links,
            x: $x,
            attachables: $attachables,
        );
    }
}
