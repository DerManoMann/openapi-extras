<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Concerns;

use OpenApi\Generator;
use Symfony\Component\Finder\Finder;

trait Fixtures
{
    public function fixturesProvider(): iterable
    {
        $namespace = 'Radebatz\\OpenApi\\Extras\\Annotations';
        yield 'annotations' => [
            (new Generator())
                ->addNamespace($namespace . '\\')
                ->addAlias('oax', $namespace),
            $this->getAnnotationsFinder(),
        ];

        yield 'attributes' => [
            new Generator(),
            $this->getAttributesFinder(),
        ];
    }

    protected function getAnnotationsFinder(): Finder
    {
        return (new Finder())
            ->in(__DIR__ . '/../Fixtures')
            ->name('*.php')
            ->exclude(['Models', 'Controllers/Attributes']);
    }

    protected function getAttributesFinder(): Finder
    {
        return (new Finder())
            ->in(__DIR__ . '/../Fixtures')
            ->name('*.php')
            ->exclude(['Models', 'Controllers/Annotations']);
    }
}
