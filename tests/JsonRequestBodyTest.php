<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Annotations\JsonRequestBody as JsonRequestBodyAnnotation;
use Radebatz\OpenApi\Extras\Attributes\JsonRequestBody;
use Radebatz\OpenApi\Extras\Processors\AugmentJsonRequestBody;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Models\TokenPairResource;

class JsonRequestBodyTest extends TestCase
{
    public function testAttributeWithRef(): void
    {
        $requestBody = new JsonRequestBody(ref: TokenPairResource::class);

        $this->assertNotEmpty($requestBody->_unmerged);
        $this->assertInstanceOf(OA\JsonContent::class, $requestBody->_unmerged[0]);
    }

    public function testAttributeExplicitDescription(): void
    {
        $requestBody = new JsonRequestBody(ref: TokenPairResource::class, description: 'Custom desc');

        $this->assertEquals('Custom desc', $requestBody->description);
    }

    public function testAttributeNoRef(): void
    {
        $requestBody = new JsonRequestBody();

        $this->assertEquals(Generator::UNDEFINED, $requestBody->description);
    }

    public function testAttributeRequired(): void
    {
        $requestBody = new JsonRequestBody(ref: TokenPairResource::class, required: true);

        $this->assertTrue($requestBody->required);
    }

    public function testAnnotationWithRef(): void
    {
        $requestBody = new JsonRequestBodyAnnotation([
            'ref' => TokenPairResource::class,
        ]);

        $this->assertNotEmpty($requestBody->_unmerged);
        $this->assertInstanceOf(OA\JsonContent::class, $requestBody->_unmerged[0]);
    }

    public function testProcessorResolvesDescriptionFromTitle(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, 'Token pair', Generator::UNDEFINED);

        $requestBody = new JsonRequestBody(ref: TokenPairResource::class);
        $analysis->addAnnotation($requestBody, new Context([]));

        (new AugmentJsonRequestBody())($analysis);

        $this->assertEquals('Token pair', $requestBody->description);
    }

    public function testProcessorResolvesDescriptionFromSchemaDescription(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, Generator::UNDEFINED, 'A token pair request');

        $requestBody = new JsonRequestBody(ref: TokenPairResource::class);
        $analysis->addAnnotation($requestBody, new Context([]));

        (new AugmentJsonRequestBody())($analysis);

        $this->assertEquals('A token pair request', $requestBody->description);
    }

    public function testProcessorFallsBackToClassName(): void
    {
        $analysis = new Analysis([], new Context([]));

        $requestBody = new JsonRequestBody(ref: 'App\\Models\\SomeUnknownClass');
        $analysis->addAnnotation($requestBody, new Context([]));

        (new AugmentJsonRequestBody())($analysis);

        $this->assertEquals('SomeUnknownClass', $requestBody->description);
    }

    public function testProcessorSkipsExplicitDescription(): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, 'Token pair', Generator::UNDEFINED);

        $requestBody = new JsonRequestBody(ref: TokenPairResource::class, description: 'My desc');
        $analysis->addAnnotation($requestBody, new Context([]));

        (new AugmentJsonRequestBody())($analysis);

        $this->assertEquals('My desc', $requestBody->description);
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
