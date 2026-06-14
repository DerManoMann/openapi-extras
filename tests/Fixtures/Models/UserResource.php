<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Models;

use OpenApi\Attributes as OAT;

#[OAT\Schema(schema: 'UserResource')]
class UserResource
{
    #[OAT\Property(property: 'id', type: 'integer')]
    public int $id;

    #[OAT\Property(property: 'name', type: 'string')]
    public string $name;

    #[OAT\Property(property: 'email', type: 'string')]
    public string $email;
}
