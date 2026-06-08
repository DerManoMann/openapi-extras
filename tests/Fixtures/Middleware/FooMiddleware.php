<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware;

class FooMiddleware
{
    public function __invoke()
    {
        return 'foo';
    }
}
