<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Controllers\Attributes;

use Radebatz\OpenApi\Extras\Tests\Fixtures\Models\TokenPairResource;

class ParameterController
{
    public function create(TokenPairResource $resource): void
    {
    }
}
