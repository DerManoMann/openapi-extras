<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;

/**
 * @OAX\Controller(
 *     @OAX\Middleware(names={"Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\FooMiddleware"})
 * )
 */
class MiddlewareController
{
    /**
     * @OA\Get(
     *     path="/mw",
     *     @OA\Response(response="200", description="All good"),
     *     @OAX\Middleware(names={"Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\BarMiddleware"})
     * )
     */
    public function mw(): mixed
    {
        return 'mw';
    }
}
