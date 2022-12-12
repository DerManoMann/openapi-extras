<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;

class MiddlewareController
{
    /**
     * @OA\Get(
     *     path="/mw",
     *     @OAX\Middleware(names={"Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\FooMiddleware", "Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\BarMiddleware"}),
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function mw(): mixed
    {
        return 'mw';
    }
}
