<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller(prefix: '/standalone', inherit: false)]
#[OAT\Response(response: 500, description: 'Server error')]
class NoInheritController extends AbstractBaseController
{
    #[OAT\Get(path: '/isolated', operationId: 'isolated')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function isolated()
    {
        return 'isolated';
    }
}
