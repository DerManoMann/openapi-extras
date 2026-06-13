<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\MergeJsonContent;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Attributes\JsonResponse;
use Radebatz\OpenApi\Extras\Processors\AugmentJsonResponse;
use Radebatz\OpenApi\Extras\Processors\WrapJsonResponseContent;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Models\TokenPairResource;

class JsonResponseTest extends TestCase
{
    public static function wrapKeyProvider(): array
    {
        return [
            'default wrap key' => ['data'],
            'custom wrap key' => ['result'],
        ];
    }

    /**
     * @dataProvider wrapKeyProvider
     */
    public function testWrapsRefInEnvelope(string $wrap): void
    {
        $analysis = $this->createAnalysisWithSchema(TokenPairResource::class, 'Token pair', Generator::UNDEFINED);

        $response = new JsonResponse(response: 200, ref: TokenPairResource::class, wrap: $wrap);
        $analysis->addAnnotation($response, new Context([]));

        $this->runPipeline($analysis);

        $schema = $response->content['application/json']->schema;
        $this->assertEquals([$wrap], $schema->required);
        $this->assertCount(1, $schema->properties);
        $this->assertEquals($wrap, $schema->properties[0]->property);
    }

    public static function descriptionProvider(): array
    {
        return [
            'from schema title' => [TokenPairResource::class, 'Token pair', Generator::UNDEFINED, null, 'Token pair'],
            'from schema description' => [TokenPairResource::class, Generator::UNDEFINED, 'A response', null, 'A response'],
            'falls back to class name' => ['App\\Models\\SomeUnknown', Generator::UNDEFINED, Generator::UNDEFINED, null, 'SomeUnknown'],
            'explicit not overridden' => [TokenPairResource::class, 'Token pair', Generator::UNDEFINED, 'My desc', 'My desc'],
        ];
    }

    /**
     * @dataProvider descriptionProvider
     */
    public function testDescriptionResolution(string $ref, string $title, string $schemaDesc, ?string $explicit, string $expected): void
    {
        if ($ref === 'App\\Models\\SomeUnknown') {
            $analysis = new Analysis([], new Context([]));
        } else {
            $analysis = $this->createAnalysisWithSchema($ref, $title, $schemaDesc);
        }

        $response = new JsonResponse(response: 200, ref: $ref, description: $explicit);
        $analysis->addAnnotation($response, new Context([]));

        (new AugmentJsonResponse())($analysis);

        $this->assertEquals($expected, $response->description);
    }

    public function testPlainResponseUnaffected(): void
    {
        $analysis = new Analysis([], new Context([]));

        $context = new Context([]);
        $response = new OA\Response(['response' => 200, 'description' => 'OK', '_context' => $context]);
        $jsonContent = new OA\JsonContent(['ref' => TokenPairResource::class, '_context' => new Context(['nested' => $response], $context)]);
        $analysis->addAnnotation($response, $context);
        $analysis->addAnnotation($jsonContent, $jsonContent->_context);

        (new MergeJsonContent())($analysis);
        $schemaBefore = $response->content['application/json']->schema;

        (new AugmentJsonResponse())($analysis);
        (new WrapJsonResponseContent())($analysis);

        $this->assertSame($schemaBefore, $response->content['application/json']->schema);
    }

    protected function runPipeline(Analysis $analysis): void
    {
        (new AugmentJsonResponse())($analysis);
        (new MergeJsonContent())($analysis);
        (new WrapJsonResponseContent())($analysis);
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
