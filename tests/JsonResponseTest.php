<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Annotations\JsonResponse as JsonResponseAnnotation;
use Radebatz\OpenApi\Extras\Attributes\JsonResponse;
use Radebatz\OpenApi\Extras\Processors\AugmentJsonResponse;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Models\TokenPairResource;

class JsonResponseTest extends TestCase
{
    public function testAttributeWithRef(): void
    {
        $response = new JsonResponse(response: 200, ref: TokenPairResource::class);

        $this->assertEquals(200, $response->response);
        $this->assertNotEmpty($response->_unmerged);
        $this->assertInstanceOf(OA\JsonContent::class, $response->_unmerged[0]);
    }

    public function testAttributeExplicitDescription(): void
    {
        $response = new JsonResponse(response: 200, ref: TokenPairResource::class, description: 'Custom desc');

        $this->assertEquals('Custom desc', $response->description);
    }

    public function testAttributeNoRef(): void
    {
        $response = new JsonResponse(response: 204);

        $this->assertEquals(Generator::UNDEFINED, $response->description);
    }

    public function testAnnotationWithRef(): void
    {
        $response = new JsonResponseAnnotation([
            'response' => 200,
            'ref' => TokenPairResource::class,
        ]);

        $this->assertEquals(200, $response->response);
        $this->assertNotEmpty($response->_unmerged);
        $this->assertInstanceOf(OA\JsonContent::class, $response->_unmerged[0]);
    }

    public function testProcessorResolvesDescriptionFromTitle(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, 'Token pair', Generator::UNDEFINED);

        $response = new JsonResponse(response: 200, ref: TokenPairResource::class);
        $analysis->addAnnotation($response, new Context([]));

        (new AugmentJsonResponse())($analysis);

        $this->assertEquals('Token pair', $response->description);
    }

    public function testProcessorResolvesDescriptionFromSchemaDescription(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, Generator::UNDEFINED, 'A token pair response');

        $response = new JsonResponse(response: 200, ref: TokenPairResource::class);
        $analysis->addAnnotation($response, new Context([]));

        (new AugmentJsonResponse())($analysis);

        $this->assertEquals('A token pair response', $response->description);
    }

    public function testProcessorFallsBackToClassName(): void
    {
        $analysis = new Analysis([], new Context([]));

        $response = new JsonResponse(response: 200, ref: 'App\\Models\\SomeUnknownClass');
        $analysis->addAnnotation($response, new Context([]));

        (new AugmentJsonResponse())($analysis);

        $this->assertEquals('SomeUnknownClass', $response->description);
    }

    public function testProcessorSkipsExplicitDescription(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, 'Token pair', Generator::UNDEFINED);

        $response = new JsonResponse(response: 200, ref: TokenPairResource::class, description: 'My desc');
        $analysis->addAnnotation($response, new Context([]));

        (new AugmentJsonResponse())($analysis);

        $this->assertEquals('My desc', $response->description);
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
}
