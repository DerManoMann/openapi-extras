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

        $allNames = $this->collectMiddlewareNames($operation);
        $this->assertContains(BarMiddleware::class, $allNames);
        $this->assertContains(FooMiddleware::class, $allNames);
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

        $allNames = $this->collectMiddlewareNames($operation);
        $this->assertContains(FooMiddleware::class, $allNames);
        $this->assertContains(BarMiddleware::class, $allNames);
        $this->assertContains('auth:superadmin', $allNames);
        $this->assertContains('auth:admin', $allNames);
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

    /**
     * @dataProvider fixturesProvider
     */
    public function testMiddlewareOrderPreserved(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'inheritedList');

        $allNames = $this->collectMiddlewareNames($operation);

        $fooPos = array_search(FooMiddleware::class, $allNames);
        $barPos = array_search(BarMiddleware::class, $allNames);
        $this->assertLessThan($barPos, $fooPos, 'Base middleware should appear before child middleware');
    }

    public function testMiddlewareOperationOverridesController(): void
    {
        $processor = new MergeControllerDefaults();

        $controllerMiddleware = new OAX\Middleware(['names' => ['auth', 'rate-limit']]);
        $operationMiddleware = new OAX\Middleware(['names' => ['auth', 'cache']]);

        $context = new Context(['namespace' => 'App\\Http', 'class' => 'UserController']);
        $controller = new OAX\Controller([
            'prefix' => '/api',
            '_context' => $context,
        ]);
        $controller->attachables = [$controllerMiddleware];

        $operation = new OA\Get([
            'path' => '/list',
            '_context' => new Context(['namespace' => 'App\\Http', 'class' => 'UserController', 'method' => 'list']),
        ]);
        $operation->attachables = [$operationMiddleware];

        $analysis = new Analysis([], new Context([]));
        $analysis->addAnnotation($controller, $context);
        $analysis->addAnnotation($operation, $operation->_context);
        $analysis->addClassDefinition(['class' => 'UserController', 'context' => $context]);

        $processor($analysis);

        $allNames = $this->collectMiddlewareNames($operation);

        // 'auth' on both controller and operation; operation wins
        $this->assertContains('auth', $allNames);
        $this->assertContains('rate-limit', $allNames);
        $this->assertContains('cache', $allNames);

        // Verify the 'auth' entry points to the operation-level instance
        $middlewares = array_filter(
            $operation->attachables,
            fn ($a) => $a instanceof OAX\Middleware && in_array('auth', $a->names)
        );
        $authMiddleware = reset($middlewares);
        $this->assertSame($operationMiddleware, $authMiddleware);
    }

    protected function collectMiddlewareNames(OA\Operation $operation): array
    {
        $this->assertIsArray($operation->attachables);
        $names = [];
        foreach ($operation->attachables as $attachable) {
            if ($attachable instanceof OAX\Middleware) {
                $names = array_merge($names, $attachable->names ?? []);
            }
        }

        return $names;
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

        $operations = $analysis->getAnnotationsOfType(OA\Operation::class);
        foreach ($operations as $operation) {
            if ($operation->operationId === $operationId) {
                return $operation;
            }
        }

        $this->fail(sprintf('Operation "%s" not found', $operationId));
    }
}
