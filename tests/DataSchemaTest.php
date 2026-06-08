<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests;

use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Tests\Concerns\ComparesSpecs;
use Symfony\Component\Finder\Finder;

class DataSchemaTest extends TestCase
{
    use ComparesSpecs;

    public function testDataSchema(): void
    {
        $finder = (new Finder())
            ->in(__DIR__ . '/Fixtures/Models')
            ->name('UserResource.php');

        $openapi = (new Generator())->generate($finder, validate: false);

        $expected = <<<'YAML'
openapi: 3.0.0
components:
  schemas:
    UserResource:
      required:
        - data
      properties:
        data:
          required:
            - id
            - name
          properties:
            id:
              type: integer
              nullable: false
            name:
              type: string
              nullable: false
            email:
              type: string
          type: object
      type: object
YAML;

        $this->assertSpecEquals($openapi, $expected);
    }

    public function testDataSchemaAnnotation(): void
    {
        $namespace = 'Radebatz\\OpenApi\\Extras\\Annotations';
        $finder = (new Finder())
            ->in(__DIR__ . '/Fixtures/Models')
            ->name('UserResourceAnnotation.php');

        $openapi = (new Generator())
            ->addNamespace($namespace . '\\')
            ->addAlias('oax', $namespace)
            ->generate($finder, validate: false);

        $expected = <<<'YAML'
openapi: 3.0.0
components:
  schemas:
    UserResourceAnnotation:
      required:
        - data
      properties:
        data:
          required:
            - id
            - name
          properties:
            id:
              type: integer
              nullable: false
            name:
              type: string
              nullable: false
            email:
              type: string
          type: object
      type: object
YAML;

        $this->assertSpecEquals($openapi, $expected);
    }

    public function testDataSchemaNoRequired(): void
    {
        $finder = (new Finder())
            ->in(__DIR__ . '/Fixtures/Models')
            ->name('SimpleResource.php');

        $openapi = (new Generator())->generate($finder, validate: false);

        $expected = <<<'YAML'
openapi: 3.0.0
components:
  schemas:
    SimpleResource:
      required:
        - data
      properties:
        data:
          properties:
            label:
              type: string
          type: object
      type: object
YAML;

        $this->assertSpecEquals($openapi, $expected);
    }
}
