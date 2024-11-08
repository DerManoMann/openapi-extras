<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Models;

use OpenApi\Attributes as OAT;

#[OAT\Schema()]
class EnumProperties
{
    #[OAT\Property(enum: SimpleEnum::class)]
    protected SimpleEnum $simple;

    #[OAT\Property(enum: AnimalEnum::class)]
    protected AnimalEnum $animal;
}
