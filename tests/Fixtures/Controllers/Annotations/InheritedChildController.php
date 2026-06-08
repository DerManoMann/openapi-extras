<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware as Middleware;

/**
 * @OAX\Controller(
 *     prefix="/users",
 *     @OA\Response(response="404", description="Not found"),
 *     @OAX\Middleware(names={Middleware\BarMiddleware::class, "auth:superadmin"})
 * )
 */
class InheritedChildController extends AbstractBaseController
{
    /**
     * @OA\Get(
     *     path="/list",
     *     operationId="inheritedList",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function list()
    {
        return 'list';
    }
}
