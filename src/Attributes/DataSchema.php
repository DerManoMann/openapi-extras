<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace Radebatz\OpenApi\Extras\Attributes;

use OpenApi\Attributes as OA;
use OpenApi\Generator;

#[\Attribute(\Attribute::TARGET_CLASS)]
class DataSchema extends OA\Schema
{
    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'data_property'];

    protected OA\Property $data_property;

    /**
     * @param list<string>             $required
     * @param list<OA\Property>        $properties
     * @param array<string,mixed>|null $x
     * @param OA\Attachable[]|null     $attachables
     */
    public function __construct(
        ?string $schema = null,
        ?string $title = null,
        ?string $description = Generator::UNDEFINED,
        array $required = [],
        array $properties = [],
        ?array $x = null,
        ?array $attachables = null,
    ) {
        $this->data_property = new OA\Property(
            property: 'data',
            required: $required ?: null,
            properties: $properties ?: null,
            type: 'object',
        );

        parent::__construct(
            schema: $schema,
            title: $title,
            description: $description,
            required: ['data'],
            properties: [$this->data_property],
            x: $x,
            attachables: $attachables,
        );
    }

    public function merge(array $annotations, bool $ignore = false): array
    {
        $forwarded = [];
        $remaining = [];

        foreach ($annotations as $annotation) {
            if ($annotation instanceof OA\Property) {
                $forwarded[] = $annotation;
            } else {
                $remaining[] = $annotation;
            }
        }

        if ($forwarded !== []) {
            if (Generator::isDefault($this->data_property->properties)) {
                $this->data_property->properties = [];
            }

            foreach ($forwarded as $property) {
                $this->data_property->properties[] = $property;

                if ($property->nullable === false) {
                    $name = Generator::isDefault($property->property)
                        ? $property->_context->property ?? null
                        : ($property->property);

                    if ($name) {
                        if (Generator::isDefault($this->data_property->required)) {
                            $this->data_property->required = [];
                        }
                        if (!in_array($name, $this->data_property->required, true)) {
                            $this->data_property->required[] = $name;
                        }
                    }
                }
            }
        }

        return parent::merge($remaining, $ignore);
    }
}
