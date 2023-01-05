<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller()]
#[OAT\Response(response: 403, description: 'Not allowed')]
class ResponseController
{
    #[OAT\Get(path: '/response', operationId: 'response')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function response()
    {
        return 'response';
    }
}
