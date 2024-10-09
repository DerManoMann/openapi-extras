<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests;

use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;
use Symfony\Component\Finder\Finder;

class AnnotationsTest extends TestCase
{
    use Concerns\ComparesSpecs;
    use Concerns\UsesAnnotations;
    use Concerns\UsesAttributes;

    public function specTestProvider(): iterable
    {
        $namespace = 'Radebatz\\OpenApi\\Extras\\Annotations';

        yield 'annotations' => [
            (new Generator())
                ->addNamespace($namespace . '\\')
                ->addAlias('oax', $namespace),
            $this->getAnnotationFinder(),
        ];

        if (\PHP_VERSION_ID >= 80100) {
            yield 'attributes' => [
                new Generator(),
                $this->getAttributesFinder(),
            ];
        }
    }

    /**
     * @dataProvider specTestProvider
     */
    public function testAnnotations(Generator $generator, Finder $finder): void
    {
        $openapi = $generator
            ->addProcessor(new MergeControllerDefaults(), BuildPaths::class)
            ->generate($finder);
        $specFile = __DIR__ . '/Fixtures/spec.yaml';
        // file_put_contents($specFile, $openapi->toYaml());
        $this->assertSpecEquals(
            $openapi,
            file_get_contents($specFile)
        );
    }
}
