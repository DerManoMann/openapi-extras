<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;

/**
 * @OAX\Controller(
 *     prefix="/foo",
 *     @OA\Response(response="401", description="Unauthorized")
 * )
 */
class InvokeController
{
    /**
     * @OA\Get(
     *     path="/invoke/{name}",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function __invoke(string $name): mixed
    {
        return 'invoke: ' . $name;
    }
}
