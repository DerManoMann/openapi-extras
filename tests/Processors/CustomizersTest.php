<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Processors;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Radebatz\OpenApi\Extras\Processors\Customizers;

class CustomizersTest extends TestCase
{
    public function testMappings(): void
    {
        $generator = new Generator(new NullLogger());
        $generator->getProcessorPipeline()->add(new Customizers());
        $generator->setConfig([
            'customizers' => [
                'mappings' => [
                    OA\OpenApi::class => [fn (OA\OpenApi $openApi) => $openApi->openapi = OA\OpenApi::VERSION_3_1_0],
                ],
            ],
        ]);

        $openapi = $generator->generate([__DIR__ . '/../Fixtures/OpenApi.php']);

        $this->assertEquals(OA\OpenApi::VERSION_3_1_0, $openapi->openapi);
    }
}
