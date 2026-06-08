<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Models;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\DataSchema(schema: 'UserResource', required: ['id', 'name'])]
class UserResource
{
    #[OAT\Property(property: 'id', type: 'integer', nullable: false)]
    public int $id;

    #[OAT\Property(property: 'name', type: 'string', nullable: false)]
    public string $name;

    #[OAT\Property(property: 'email', type: 'string')]
    public string $email;
}
