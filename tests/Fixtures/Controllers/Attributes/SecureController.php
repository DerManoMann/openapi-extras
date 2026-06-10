<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\SecureMiddleware;

#[OAX\Controller(middlewares: [new SecureMiddleware()])]
class SecureController
{
    #[OAT\Get(path: '/secure', operationId: 'secureEndpoint')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function secure()
    {
        return 'secure';
    }

    #[OAT\Get(path: '/also-secure', operationId: 'alsoSecureEndpoint')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function alsoSecure()
    {
        return 'also-secure';
    }
}
