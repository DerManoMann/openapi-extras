<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware as Middleware;

/**
 * @OAX\Controller(
 *     @OAX\Middleware(names={Middleware\FooMiddleware::class})
 * )
 */
class MiddlewareController
{
    /**
     * @OA\Get(
     *     path="/mw",
     *     operationId="mw",
     *     @OA\Response(response="200", description="All good"),
     *     @OAX\Middleware(names={Middleware\BarMiddleware::class})
     * )
     */
    public function mw()
    {
        return 'mw';
    }
}
