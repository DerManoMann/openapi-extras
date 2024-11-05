<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;
use Radebatz\OpenApi\Extras\Tests\Concerns\ComparesSpecs;
use Radebatz\OpenApi\Extras\Tests\Concerns\Fixtures;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\FooMiddleware;
use Symfony\Component\Finder\Finder;

class MergeControllerDefaultsTest extends TestCase
{
    use ComparesSpecs;
    use Fixtures;

    /**
     * @dataProvider fixturesProvider
     */
    public function testMergeMiddlewares(Generator $generator, Finder $finder): void
    {
        $generator
            ->getProcessorPipeline()
            ->insert(new MergeControllerDefaults(), BuildPaths::class);
        $analysis = $generator
            ->withContext(function (Generator $generator, Analysis $analysis, Context $context) use ($finder) {
                $generator->generate($finder, $analysis);

                return $analysis;
            });

        /** @var OA\Operation[] $operations */
        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);

        $this->assertNotEmpty($operations);
        foreach ($operations as $operation) {
            if ($operation->path === '/mw') {
                $this->assertIsArray($operation->attachables);
                $this->assertCount(2, $operation->attachables);
                $this->assertInstanceOf(OAX\Middleware::class, $operation->attachables[0]);
                $this->assertEquals([BarMiddleware::class], $operation->attachables[0]->names);
                $this->assertInstanceOf(OAX\Middleware::class, $operation->attachables[1]);
                $this->assertEquals([FooMiddleware::class], $operation->attachables[1]->names);
            }
        }
    }
}
