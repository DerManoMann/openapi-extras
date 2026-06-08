<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\BarMiddleware;

#[OAX\Controller(prefix: '/users')]
#[OAT\Response(response: 404, description: 'Not found')]
#[OAX\Middleware([BarMiddleware::class, 'auth:superadmin'])]
class InheritedChildController extends AbstractBaseController
{
    #[OAT\Get(path: '/list', operationId: 'inheritedList')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function list()
    {
        return 'list';
    }
}
