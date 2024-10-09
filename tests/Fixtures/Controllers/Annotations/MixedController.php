<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware as Middleware;

/**
 * @OAX\Controller(
 *     @OAX\Middleware(names={Middleware\FooMiddleware::class}),
 *     @OA\Header(
 *         header="X-Shared",
 *         description="Shared header",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response="403", description="Not allowed")
 * )
 */
class MixedController
{
    /**
     * @OA\Get(
     *     path="/mixed",
     *     operationId="mixed",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function mixed()
    {
        return 'mixed';
    }
}
