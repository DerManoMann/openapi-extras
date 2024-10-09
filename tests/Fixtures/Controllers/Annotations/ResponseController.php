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
     *     path="/response1",
     *     operationId="response1",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function response1()
    {
        return 'response1';
    }

    /**
     * @OA\Post(
     *     path="/response2",
     *     operationId="response2",
     *     @OA\Response(response="400", description="Invalid")
     * )
     */
    public function response2()
    {
        return 'response2';
    }
}
