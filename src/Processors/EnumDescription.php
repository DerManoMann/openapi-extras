<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * Generates a description for enum based properties.
 */
class EnumDescription
{
    protected bool $enabled;

    public function __construct(bool $enabled = false)
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enables/disables the <code>EnumDescription</code> processor.
     */
    public function setEnabled(bool $enabled): EnumDescription
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function __invoke(Analysis $analysis)
    {
        if (!class_exists('\\ReflectionEnum')) {
            return;
        }

        /** @var OA\Property[] $properties */
        $properties = $analysis->getAnnotationsOfType(OA\Property::class);

        foreach ($properties as $property) {
            if (!Generator::isDefault($property->enum) && enum_exists($property->enum) && Generator::isDefault($property->description)) {
                $re = new \ReflectionEnum($property->enum);
                $values = [];
                if ($re->isBacked()) {
                    foreach ($re->getCases() as $case) {
                        $values[] = $case->getBackingValue() . ':' . $case->getName();
                    }
                } else {
                    foreach ($re->getCases() as $case) {
                        $values[] = $case->getName();
                    }
                }

                $property->description = $re->getShortName() . ' (' . implode('; ', $values) . ')';
            }
        }
    }
}
