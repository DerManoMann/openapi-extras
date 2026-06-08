<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Models;

use OpenApi\Attributes as OAT;
use Radebatz\OpenApi\Extras\Attributes as OAX;

#[OAX\DataSchema(schema: 'SimpleResource')]
class SimpleResource
{
    #[OAT\Property(property: 'label', type: 'string')]
    public string $label;
}
