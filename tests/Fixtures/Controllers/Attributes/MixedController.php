<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\FooMiddleware;

#[OAX\Controller()]
#[OAX\Middleware([FooMiddleware::class])]
#[OAT\Header(
    header: 'X-Shared',
    description: 'Shared header',
    schema: new OAT\Schema(type: 'string')
)]
#[OAT\Response(response: 403, description: 'Not allowed')]
class MixedController
{
    #[OAT\Get(path: '/mixed', operationId: 'mixed')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function mixed()
    {
        return 'mixed';
    }
}
