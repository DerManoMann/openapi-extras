<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware as Middleware;

/**
 * @OAX\Controller(
 *     prefix="/api/v2",
 *     tags={"api"},
 *     @OA\Response(response="403", description="Not allowed"),
 *     @OA\Header(header="X-Request-Id", description="Request ID", @OA\Schema(type="string")),
 *     @OAX\Middleware(names={Middleware\FooMiddleware::class, "auth:admin"})
 * )
 */
abstract class AbstractBaseController
{
}
