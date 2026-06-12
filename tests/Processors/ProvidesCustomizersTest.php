<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\BuildPaths;
use PHPUnit\Framework\TestCase;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Processors\Customizers;
use Radebatz\OpenApi\Extras\Processors\MergeControllerDefaults;
use Radebatz\OpenApi\Extras\ProvidesCustomizersInterface;
use Radebatz\OpenApi\Extras\Tests\Concerns\Fixtures;
use Symfony\Component\Finder\Finder;

class ProvidesCustomizersTest extends TestCase
{
    use Fixtures;

    /**
     * @dataProvider fixturesProvider
     */
    public function testSecurityAppliedFromMiddleware(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'secureEndpoint');

        $this->assertEquals([['bearerAuth' => []]], $operation->security);
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testSecurityAppliedToAllControllerOperations(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'alsoSecureEndpoint');

        $this->assertEquals([['bearerAuth' => []]], $operation->security);
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testMiddlewareStillAttached(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'secureEndpoint');

        $this->assertIsArray($operation->attachables);
        $middleware = null;
        foreach ($operation->attachables as $attachable) {
            if ($attachable instanceof OAX\Middleware) {
                $middleware = $attachable;
                break;
            }
        }
        $this->assertNotNull($middleware);
        $this->assertContains('jwt-auth', $middleware->names);
    }

    /**
     * @dataProvider fixturesProvider
     */
    public function testNoSecurityOnUnrelatedOperation(Generator $generator, Finder $finder): void
    {
        $operation = $this->getOperation($generator, $finder, 'mw');

        $this->assertTrue(Generator::isDefault($operation->security));
    }

    protected function getOperation(Generator $generator, Finder $finder, string $operationId): OA\Operation
    {
        $generator->getProcessorPipeline()
            ->insert(new MergeControllerDefaults(), BuildPaths::class)
            ->add(new Customizers());

        $generator->setConfig([
            'customizers' => [
                'mappings' => [
                    OA\Operation::class => [
                        static function (OA\Operation $operation): void {
                            if (Generator::isDefault($operation->attachables)) {
                                return;
                            }
                            foreach ($operation->attachables as $attachable) {
                                if (!($attachable instanceof ProvidesCustomizersInterface)) {
                                    continue;
                                }
                                foreach ($attachable::customizers() as $annotationClass => $callable) {
                                    if ($operation instanceof $annotationClass) {
                                        $callable($operation);
                                    }
                                }
                            }
                        },
                    ],
                ],
            ],
        ]);

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
