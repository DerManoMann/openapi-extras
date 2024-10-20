<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use OpenApi\Processors\AugmentTags;
use OpenApi\Processors\CleanUnusedComponents;
use OpenApi\Processors\OperationId;
use OpenApi\Processors\PathFilter;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\OpenApiBuilder;
use Radebatz\OpenApi\Extras\Processors\Customizers;

class OpenApiBuilderTest extends TestCase
{
    protected function getPipes(Generator $generator): array
    {
        $pipeline = $generator->getProcessorPipeline();
        $rp = new \ReflectionProperty($pipeline, 'pipes');
        $rp->setAccessible(true);

        return $rp->getValue($pipeline);
    }

    protected function getPipe(array $pipes, string $pipeClass)
    {
        foreach ($pipes as $pipe) {
            if ($pipe instanceof $pipeClass) {
                return $pipe;
            }
        }

        return null;
    }

    public function testPathFilterConfigMixed(): void
    {
        $builder = (new OpenApiBuilder())
            ->pathsToMatch('foo')
            ->tagsToMatch(['bar']);
        $pipes = $this->getPipes($builder->build());
        $pathFilter = $this->getPipe($pipes, PathFilter::class);

        $this->assertNotNull($pathFilter);
        $this->assertEquals(['foo'], $pathFilter->getPaths());
        $this->assertEquals(['bar'], $pathFilter->getTags());
    }

    public function testClearUnused(): void
    {
        $builder = (new OpenApiBuilder())
            ->clearUnused(true);
        $pipes = $this->getPipes($builder->build());
        $cleanUnusedComponents = $this->getPipe($pipes, CleanUnusedComponents::class);

        $this->assertNotNull($cleanUnusedComponents);
        $this->assertTrue($cleanUnusedComponents->isEnabled());
    }

    public function testOperationIdHashing(): void
    {
        $builder = (new OpenApiBuilder())
            ->operationIdHashing(false);
        $pipes = $this->getPipes($builder->build());
        $operationId = $this->getPipe($pipes, OperationId::class);

        $this->assertNotNull($operationId);
        $this->assertFalse($operationId->isHash());
    }

    public function testTagWhitelist(): void
    {
        $builder = (new OpenApiBuilder())
            ->tagWhitelist(['ding']);
        $pipes = $this->getPipes($builder->build());
        $augmentTags = $this->getPipe($pipes, AugmentTags::class);

        $this->assertNotNull($augmentTags);

        $rp = new \ReflectionProperty($augmentTags, 'whitelist');
        $rp->setAccessible(true);

        $this->assertEquals(['ding'], $rp->getValue($augmentTags));
    }

    public function testAddCustomizer(): void
    {
        $builder = (new OpenApiBuilder())
            ->addCustomizer(OA\Info::class, fn () => true);
        $pipes = $this->getPipes($builder->build());
        $customizers = $this->getPipe($pipes, Customizers::class);

        $this->assertNotNull($customizers);

        $mappings = $customizers->getMappings();
        $this->assertCount(1, $mappings);
    }

    public function testAddCustomizerInvalidClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new OpenApiBuilder())
            ->addCustomizer(OA\Info::class, fn () => true);
    }
}
