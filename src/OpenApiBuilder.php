<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use OpenApi\Processors\ExpandEnums;
use Psr\Log\LoggerInterface;
use Radebatz\OpenApi\Extras\Processors\Customizers;
use Radebatz\OpenApi\Extras\Processors\EnumDescription;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;

/**
 * A simple `builder` wrapper around the OpenApi `Generator` class.
 *
 * Provides explicit programmatic access to configuring the processors  of the `swagger-php` library.
 */
class OpenApiBuilder
{
    protected array $customizers = [];
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config + [
                'enumDescription' => ['enabled' => false],
            ];
    }

    /**
     * Only process endpoints matching the given `$paths` patterns.
     *
     * @param string|array $paths
     */
    public function pathsToMatch($paths): OpenApiBuilder
    {
        if (!array_key_exists('pathFilter', $this->config)) {
            $this->config['pathFilter'] = [];
        }

        $this->config['pathFilter']['paths'] = (array) $paths;

        return $this;
    }

    /**
     * Only process endpoints matching the given `$tags` patterns.
     *
     * @param string|array $tags
     */
    public function tagsToMatch($tags): OpenApiBuilder
    {
        if (!array_key_exists('pathFilter', $this->config)) {
            $this->config['pathFilter'] = [];
        }

        $this->config['pathFilter']['tags'] = (array) $tags;

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
     * Enable/disable generation of enum descriptions.
     */
    public function enumDescription(bool $enabled = true): OpenApiBuilder
    {
        $this->config['enumDescription'] = ['enabled' => $enabled];

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
     * @param class-string<OA\AbstractAnnotation> $class
     */
    public function addCustomizer(string $class, callable $customizer): OpenApiBuilder
    {
        if (!is_subclass_of($class, OA\AbstractAnnotation::class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" must implement "%s".', $class, OA\AbstractAnnotation::class));
        }

        if (!array_key_exists($class, $this->customizers)) {
            $this->customizers[$class] = [];
        }

        $this->customizers[$class][] = $customizer;

        return $this;
    }

    public function build(?LoggerInterface $logger = null): Generator
    {
        $generator = new Generator($logger);
        $config = $this->config;

        if ($config['enumDescription']['enabled']) {
            $generator->getProcessorPipeline()
                ->insert(new EnumDescription(), ExpandEnums::class);
        }

        $generator->getProcessorPipeline()
            ->insert(new MergeControllerDefaults(), BuildPaths::class);

        if ($this->customizers) {
            $generator->getProcessorPipeline()
                ->add(new Customizers());
            $config['customizers']['mappings'] = $this->customizers;
        }

        return $generator
            ->setConfig($config);
    }
}
