<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\FooMiddleware;

#[OAX\Controller(
    middlewares: [new OAX\Middleware([FooMiddleware::class])]
)]
class MiddlewareController
{
    #[OAT\Get(path: '/mw', operationId: 'mw')]
    #[OAT\Response(response: 200, description: 'All good')]
    #[OAX\Middleware([BarMiddleware::class])]
    public function mw()
    {
        return 'mw';
    }
}
