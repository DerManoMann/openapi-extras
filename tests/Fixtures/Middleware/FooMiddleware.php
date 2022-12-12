<?php

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware;

class FooMiddleware
{
    public function __invoke($request, $handlerOrResponse = null, $next = null)
    {
        return 'foo';
    }
}
