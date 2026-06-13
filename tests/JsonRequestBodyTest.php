<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\MergeJsonContent;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Attributes\JsonRequestBody;
use Radebatz\OpenApi\Extras\Processors\AugmentJsonRequestBody;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Models\TokenPairResource;

class JsonRequestBodyTest extends TestCase
{
    public function testCreatesContentFromRef(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, 'Token pair', Generator::UNDEFINED);

        $requestBody = new JsonRequestBody(ref: TokenPairResource::class);
        $analysis->addAnnotation($requestBody, new Context([]));

        (new AugmentJsonRequestBody())($analysis);
        (new MergeJsonContent())($analysis);

        $this->assertIsArray($requestBody->content);
        $schema = $requestBody->content['application/json']->schema;
        $this->assertEquals(TokenPairResource::class, $schema->ref);
        $this->assertEquals('Token pair', $requestBody->description);
    }

    public function testResolvesSourceFromParameterTypeHint(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, 'Token pair', Generator::UNDEFINED);

        $requestBody = new JsonRequestBody();
        $rp = new \ReflectionParameter([Fixtures\Controllers\Attributes\ParameterController::class, 'create'], 'resource');
        $requestBody->_context = new Context(['reflector' => $rp]);
        $analysis->addAnnotation($requestBody, $requestBody->_context);

        (new AugmentJsonRequestBody())($analysis);
        (new MergeJsonContent())($analysis);

        $this->assertEquals(TokenPairResource::class, $requestBody->source);
        $schema = $requestBody->content['application/json']->schema;
        $this->assertEquals(TokenPairResource::class, $schema->ref);
    }

    public function testSetsComponentRefForRequestBodySource(): void
    {
        $analysis = $this->createAnalysisWithRequestBody('App\\Requests\\SharedCreateBody', 'SharedCreateBody');

        $requestBody = new JsonRequestBody(ref: 'App\\Requests\\SharedCreateBody');
        $analysis->addAnnotation($requestBody, new Context([]));

        (new AugmentJsonRequestBody())($analysis);

        $this->assertEquals('#/components/requestBodies/SharedCreateBody', $requestBody->ref);
    }

    public function testExplicitDescriptionNotOverridden(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, 'Token pair', Generator::UNDEFINED);

        $requestBody = new JsonRequestBody(ref: TokenPairResource::class, description: 'My desc');
        $analysis->addAnnotation($requestBody, new Context([]));

        (new AugmentJsonRequestBody())($analysis);

        $this->assertEquals('My desc', $requestBody->description);
    }

    public function testPlainRequestBodyUnaffected(): void
    {
        $analysis = new Analysis([], new Context([]));

        $requestBody = new OA\RequestBody(['description' => 'Plain body', '_context' => new Context([])]);
        $analysis->addAnnotation($requestBody, $requestBody->_context);

        (new AugmentJsonRequestBody())($analysis);

        $this->assertEquals('Plain body', $requestBody->description);
        $this->assertEquals(Generator::UNDEFINED, $requestBody->content);
    }

    protected function createAnalysisWithSchema(string $class, string $title, string $description): Analysis
    {
        $shortName = substr($class, strrpos($class, '\\') + 1);
        $namespace = substr($class, 0, strrpos($class, '\\'));

        $context = new Context(['namespace' => $namespace, 'class' => $shortName]);
        $schema = new OA\Schema([
            'schema' => $shortName,
            'title' => $title,
            'description' => $description,
            '_context' => $context,
        ]);
        $context->annotations = [$schema];

        $analysis = new Analysis([], new Context([]));
        $analysis->addClassDefinition(['class' => $shortName, 'context' => $context]);
        $analysis->addAnnotation($schema, $context);

        return $analysis;
    }

    protected function createAnalysisWithRequestBody(string $class, string $name): Analysis
    {
        $shortName = substr($class, strrpos($class, '\\') + 1);
        $namespace = substr($class, 0, strrpos($class, '\\'));

        $context = new Context(['namespace' => $namespace, 'class' => $shortName]);
        $rb = new OA\RequestBody([
            'request' => $name,
            '_context' => $context,
        ]);
        $context->annotations = [$rb];

        $analysis = new Analysis([], new Context([]));
        $analysis->addClassDefinition(['class' => $shortName, 'context' => $context]);
        $analysis->addAnnotation($rb, $context);

        return $analysis;
    }
}
