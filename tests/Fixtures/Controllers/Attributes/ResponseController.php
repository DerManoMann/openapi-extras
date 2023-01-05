<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller()]
#[OAT\Response(response: 403, description: 'Not allowed')]
class ResponseController
{
    #[OAT\Get(path: '/response1', operationId: 'response1')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function response1()
    {
        return 'response1';
    }

    #[OAT\Post(path: '/response2', operationId: 'response2')]
    #[OAT\Response(response: 400, description: 'Invalid')]
    public function response2()
    {
        return 'response2';
    }
}
