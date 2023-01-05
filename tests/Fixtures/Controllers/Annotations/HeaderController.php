<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;

/**
 * @OAX\Controller(
 *     @OA\Header(header="X-Shared", @OA\Schema(type="string"), description="Shared header")
 * )
 */
class HeaderController
{
    /**
     * @OA\Get(
     *     path="/header1",
     *     operationId="header1",
     *     @OA\Response(response="200", description="All good", @OA\Header(header="X-Custom", description="Custom header", @OA\Schema(type="string")))
     * )
     */
    public function header1()
    {
        return 'header1';
    }

    /**
     * @OA\Post(
     *     path="/header2",
     *     operationId="header2",
     *     @OA\Response(response="400", description="Invalid")
     * )
     */
    public function header2()
    {
        return 'header2';
    }
}
