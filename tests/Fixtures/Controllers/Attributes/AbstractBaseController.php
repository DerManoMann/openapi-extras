<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\FooMiddleware;

#[OAX\Controller(prefix: '/api/v2')]
#[OAT\Response(response: 403, description: 'Not allowed')]
#[OAT\Header(
    header: 'X-Request-Id',
    description: 'Request ID',
    schema: new OAT\Schema(type: 'string')
)]
#[OAX\Middleware([FooMiddleware::class, 'auth:admin'])]
abstract class AbstractBaseController
{
}
