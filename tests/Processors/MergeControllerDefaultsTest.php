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
        $operation = $this->getOperation($generator, $finder, 'mw');

        $this->assertIsArray($operation->attachables);
        $this->assertCount(1, $operation->attachables);
        $this->assertInstanceOf(OAX\Middleware::class, $operation->attachables[0]);
        $this->assertContains(BarMiddleware::class, $operation->attachables[0]->names);
        $this->assertContains(FooMiddleware::class, $operation->attachables[0]->names);
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testInheritedPrefix(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'inheritedList');

        $this->assertEquals('/api/v2/users/list', $operation->path);
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testInheritedResponses(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'inheritedList');

        $this->assertNotEquals(Generator::UNDEFINED, $operation->responses);
        $responseCodes = array_map(fn (OA\Response $r) => (int) $r->response, $operation->responses);
        // 200 from operation, 403 from parent, 404 from child
        $this->assertContains(200, $responseCodes);
        $this->assertContains(403, $responseCodes);
        $this->assertContains(404, $responseCodes);
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testInheritedHeaders(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'inheritedList');

        $this->assertNotEquals(Generator::UNDEFINED, $operation->responses);
        foreach ($operation->responses as $response) {
            if ($response->response === 200) {
                $this->assertNotEquals(Generator::UNDEFINED, $response->headers);
                $headerNames = array_map(fn (OA\Header $h) => $h->header, $response->headers);
                $this->assertContains('X-Request-Id', $headerNames);
            }
        }
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testInheritedMiddlewares(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'inheritedList');

        $this->assertIsArray($operation->attachables);
        $middleware = null;
        foreach ($operation->attachables as $attachable) {
            if ($attachable instanceof OAX\Middleware) {
                $middleware = $attachable;
                break;
            }
        }
        $this->assertNotNull($middleware);
        $this->assertContains(FooMiddleware::class, $middleware->names);
        $this->assertContains(BarMiddleware::class, $middleware->names);
        $this->assertContains('auth:superadmin', $middleware->names);
        $this->assertContains('auth:admin', $middleware->names);
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testInheritedTags(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'inheritedList');

        $this->assertIsArray($operation->tags);
        $this->assertContains('api', $operation->tags);
        $this->assertContains('users', $operation->tags);
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testNoInherit(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'isolated');

        $this->assertEquals('/standalone/isolated', $operation->path);
        $responseCodes = array_map(fn (OA\Response $r) => (int) $r->response, $operation->responses);
        $this->assertContains(200, $responseCodes);
        $this->assertContains(500, $responseCodes);
        $this->assertNotContains(403, $responseCodes);
    }

    protected function getOperation(Generator $generator, Finder $finder, string $operationId): OA\Operation
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
        foreach ($operations as $operation) {
            if ($operation->operationId === $operationId) {
                return $operation;
            }
        }

        $this->fail(sprintf('Operation "%s" not found', $operationId));
    }
}
