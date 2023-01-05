<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;

/**
 * @OAX\Controller(
 *     @OA\Response(response="403", description="Not allowed")
 * )
 */
class ResponseController
{
    /**
     * @OA\Get(
     *     path="/response",
     *     operationId="response",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function response()
    {
        return 'response';
    }
}
