<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers;

use OpenApi\Annotations as OA;

class ParametersController
{
    /**
     * @OA\Get(
     *     path="/hey/{name}",
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="The name",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function hey(string $name): mixed
    {
        return 'hey: ' . $name;
    }
}
