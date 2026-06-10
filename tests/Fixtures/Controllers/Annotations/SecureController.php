<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Annotations;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware\SecureMiddleware;

/**
 * @OAX\Controller(
 *     @SecureMiddleware
 * )
 */
class SecureController
{
    /**
     * @OA\Get(
     *     path="/secure",
     *     operationId="secureEndpoint",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function secure()
    {
        return 'secure';
    }

    /**
     * @OA\Get(
     *     path="/also-secure",
     *     operationId="alsoSecureEndpoint",
     *     @OA\Response(response="200", description="All good")
     * )
     */
    public function alsoSecure()
    {
        return 'also-secure';
    }
}
