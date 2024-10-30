<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Processors;

use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Radebatz\OpenApi\Extras\OpenApiBuilder;

/**
 * @requires PHP 8.1
 */
class EnumDescriptionTest extends TestCase
{
    // ------------------------------------------------------------------------

    protected function generate(): OpenApi
    {
        $generator = (new OpenApiBuilder())
            ->enumDescription()
            ->build(new NullLogger());

        return $generator->generate([
            __DIR__ . '/../Fixtures/AnimalEnum.php',
            __DIR__ . '/../Fixtures/SimpleEnum.php',
            __DIR__ . '/../Fixtures/EnumProperties.php',
        ]);
    }

    public function testSimpleEnum(): void
    {
        $openapi = $this->generate();

        $this->assertStringContainsString('SimpleEnum (One; Two)', $openapi->toYaml());
    }

    public function testBackedEnum(): void
    {
        $openapi = $this->generate();

        $this->assertStringContainsString('AnimalEnum (cat:Cat; dog:Dog)', $openapi->toYaml());
    }
}
