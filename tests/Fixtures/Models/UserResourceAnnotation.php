<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Models;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Annotations as OAX;

/**
 * @OAX\DataSchema(
 *     schema="UserResourceAnnotation",
 *     required={"id", "name"}
 * )
 */
class UserResourceAnnotation
{
    /**
     * @OA\Property(property="id", type="integer", nullable=false)
     */
    public int $id;

    /**
     * @OA\Property(property="name", type="string", nullable=false)
     */
    public string $name;

    /**
     * @OA\Property(property="email", type="string")
     */
    public string $email;
}
