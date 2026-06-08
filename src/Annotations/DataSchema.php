<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * @Annotation
 */
class DataSchema extends OA\Schema
{
    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'data_property'];

    protected OA\Property $data_property;

    public function __construct(array $properties)
    {
        $dataRequired = $properties['required'] ?? Generator::UNDEFINED;
        $dataProperties = $properties['properties'] ?? Generator::UNDEFINED;

        $this->data_property = new OA\Property([
            'property' => 'data',
            'required' => $dataRequired,
            'properties' => $dataProperties,
            'type' => 'object',
        ]);

        $properties['required'] = ['data'];
        $properties['properties'] = [$this->data_property];

        parent::__construct($properties);
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
