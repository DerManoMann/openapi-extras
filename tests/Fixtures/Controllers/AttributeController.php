<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers;

use OpenApi\Attributes as OA;
use Radebatz\OpenApi\Extras\Attributes as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\BarMiddleware;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\FooMiddleware;

#[OAX\Controller(prefix: '/attributes')]
/* Response gets merged into above Controller... */
#[OA\Response(response: 403, description: 'Not allowed')]
#[OAX\Middleware([FooMiddleware::class])]
class AttributeController
{
    #[OA\Get(path: '/prefixed', x: ['name' => 'attributes'])]
    #[OA\Response(response: 200, description: 'All good')]
    #[OAX\Middleware([BarMiddleware::class])]
    public function attributes(): mixed
    {
        return 'attributes';
    }
}
