<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\AbstractAnnotation;

/**
 * Allows for each annotation class to register one or more callbacks which
 * then can modify each instance of that class.
 */
class Customizers
{
    protected array $mappings = [];

    public function __construct(array $mappings = [])
    {
        $this->mappings = $mappings;
    }

    /**
     * A map of annotation classnames to callables.
     *
     * The registered callables will get called for each instance of `classname` to allow to
     * apply arbitrary customizations to the given instance.
     *
     * @param array<class-string<AbstractAnnotation>,callable> $mappings
     */
    public function setMappings(array $mappings): Customizers
    {
        $this->mappings = $mappings;

        return $this;
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }

    public function __invoke(Analysis $analysis)
    {
        foreach ($this->mappings as $classname => $callables) {
            $annotations = $analysis->getAnnotationsOfType($classname);
            foreach ($callables as $callable) {
                foreach ($annotations as $annotation) {
                    $callable($annotation);
                }
            }
        }
    }
}
