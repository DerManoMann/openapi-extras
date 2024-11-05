<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests;

use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;
use Radebatz\OpenApi\Extras\Tests\Concerns\ComparesSpecs;
use Radebatz\OpenApi\Extras\Tests\Concerns\Fixtures;
use Symfony\Component\Finder\Finder;

class AnnotationsTest extends TestCase
{
    use ComparesSpecs;
    use Fixtures;

    /**
     * @dataProvider fixturesProvider
     */
    public function testAnnotations(Generator $generator, Finder $finder): void
    {
        $generator
            ->getProcessorPipeline()
            ->insert(new MergeControllerDefaults(), BuildPaths::class);
        $openapi = $generator
            ->generate($finder);
        $specFile = __DIR__ . '/Fixtures/spec.yaml';
        // file_put_contents($specFile, $openapi->toYaml());
        $this->assertSpecEquals(
            $openapi,
            file_get_contents($specFile)
        );
    }
}
