<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures;

use OpenApi\Attributes as OA;

#[OA\Schema()]
class EnumProperties
{
    #[OA\Property(enum: SimpleEnum::class)]
    protected SimpleEnum $simple;

    #[OA\Property(enum: AnimalEnum::class)]
    protected AnimalEnum $animal;
}
