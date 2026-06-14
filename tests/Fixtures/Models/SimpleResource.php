<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Models;

use OpenApi\Attributes as OAT;

#[OAT\Schema(schema: 'SimpleResource')]
class SimpleResource
{
    #[OAT\Property(property: 'label', type: 'string')]
    public string $label;
}
