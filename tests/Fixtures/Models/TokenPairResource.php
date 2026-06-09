<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Models;

use OpenApi\Attributes as OAT;

#[OAT\Schema(schema: 'TokenPairResource', title: 'Token pair')]
class TokenPairResource
{
    #[OAT\Property(property: 'access_token', type: 'string')]
    public string $accessToken;

    #[OAT\Property(property: 'refresh_token', type: 'string')]
    public string $refreshToken;
}
