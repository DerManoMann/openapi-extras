<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller()]
#[OAT\Header(
    header: 'X-Shared',
    description: 'Shared header',
    schema: new OAT\Schema(type: 'string')
)]
class HeaderController
{
    #[OAT\Get(path: '/header1', operationId: 'header1')]
    #[OAT\Response(
        response: 200,
        description: 'All good',
        headers: [
            new OAT\Header(header: 'X-Custom', description: 'Custom header', schema: new OAT\Schema(type: 'string')),
        ]
    )]
    public function header1()
    {
        return 'header1';
    }

    #[OAT\Post(path: '/header2', operationId: 'header2')]
    #[OAT\Response(
        response: 400,
        description: 'Invalid'
    )]
    public function header2()
    {
        return 'header2';
    }
}
