<?php

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware;

class BarMiddleware
{
    public function __invoke($request, $handlerOrResponse = null, $next = null)
    {
        return 'bar';
    }
}
