<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\Controller(prefix: '/foo')]
#[OAT\Response(response: 403, description: 'Not allowed')]
class PrefixedController
{
    #[OAT\Get(path: '/prefixed')]
    #[OAT\Response(response: 200, description: 'All good')]
    public function prefixed(): mixed
    {
        return 'prefixed';
    }
}
