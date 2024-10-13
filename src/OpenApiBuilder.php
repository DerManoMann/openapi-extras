<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras;

use OpenApi\Annotations\AbstractAnnotation;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Processors\Customizers;

class OpenApiBuilder
{
    protected array $customizers = [];
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Only process endpoints matching the given `$paths` patterns.
     *
     * @param string|array $paths
     */
    public function pathsToMatch($paths): OpenApiBuilder
    {
        $this->config['pathFilter'] = ['paths' => $paths];

        return $this;
    }

    /**
     * Only process endpoints matching the given `$tags` patterns.
     *
     * @param string|array $tags
     */
    public function tagsToMatch($tags): OpenApiBuilder
    {
        $this->config['pathFilter'] = ['tags' => $tags];

        return $this;
    }

    /**
     * Enable/disable cleaning up of unused components.
     */
    public function clearUnused(bool $enabled = true): OpenApiBuilder
    {
        $this->config['cleanUnusedComponents'] = ['enabled' => $enabled];

        return $this;
    }

    /**
     * Enable/disable hashing of operation ids.
     */
    public function operationIdHashing(bool $enabled = true): OpenApiBuilder
    {
        $this->config['operationId'] = ['hash' => $enabled];

        return $this;
    }

    /**
     * List og tags to keep even if unused.
     */
    public function tagWhitelist(array $whitelist): OpenApiBuilder
    {
        $this->config['augmentTags'] = ['whitelist' => $whitelist];

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
        $generator = new Generator();
        $config = $this->config;

        if ($this->customizers) {
            $generator->getProcessorPipeline()
                ->add(new Customizers());
            $config['customizers']['mappings'] = $this->customizers;
        }

        return $generator
            ->setConfig($config);
    }
}
