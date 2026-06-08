<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;

/**
 * @OAX\Controller(
 *     prefix="/standalone",
 *     inherit=false,
 *     @OA\Response(response="500", description="Server error")
 * )
 */
class NoInheritController extends AbstractBaseController
{
    /**
     * @OA\Get(
     *     path="/isolated",
     *     operationId="isolated",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function isolated()
    {
        return 'isolated';
    }
}
