<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware;

class BarMiddleware
{
    public function __invoke()
    {
        return 'bar';
    }
}
