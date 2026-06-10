<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras;

use OpenApi\Annotations as OA;

interface ProvidesCustomizersInterface
{
    /**
     * @return array<class-string<OA\AbstractAnnotation>, callable[]>
     */
    public static function customizers(): array;
}
