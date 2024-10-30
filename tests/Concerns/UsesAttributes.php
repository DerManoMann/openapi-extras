<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Concerns;

use Symfony\Component\Finder\Finder;

trait UsesAttributes
{
    public function getAttributesFinder(): Finder
    {
        return (new Finder())
            ->in(__DIR__ . '/../Fixtures')
            ->name('*.php')
            ->exclude(['Models', 'Controllers/Annotations']);
    }
}
