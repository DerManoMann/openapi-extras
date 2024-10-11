<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras;

use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Processors\Customizers;

class OpenApiBuilder
{
    /**
     * @var string|array
     */
    protected $paths = null;
    protected array $customizers = [];

    /**
     * Only process endpoints matching the given `$paths` patterns.
     *
     * @param string|array $paths
     */
    public function pathsToMatch($paths): OpenApiBuilder
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * @param class-string<AbstractAnnotation> $class
     */
    public function addCustomizer(string $class, callable $customizer): OpenApiBuilder
    {
        if (!is_subclass_of($class, AbstractAnnotation::class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" must implement "%s".', $class, AbstractAnnotation::class));
        }

        if (!array_key_exists($class, $this->customizers)) {
            $this->customizers[$class] = [];
        }

        $this->customizers[$class][] = $customizer;

        return $this;
    }

    public function build(): Generator
    {
        $config = [];
        $generator = new Generator();

        if ($this->paths) {
            $config['pathFilter'] = ['paths' => $this->paths];
        }

        if ($this->customizers) {
            $generator->getProcessorPipeline()
                ->add(new Customizers());
            $config['customizers']['mappings'] = $this->customizers;
        }

        return $generator
            ->setConfig($config);
    }
}
