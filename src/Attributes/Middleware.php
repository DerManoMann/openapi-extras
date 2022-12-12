<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Attributes;

#[\Attribute(\Attribute::TARGET_ALL | \Attribute::IS_REPEATABLE)]
class Middleware extends \Radebatz\OpenApi\Extras\Annotations\Middleware
{
    public function __construct(array $names)
    {
        parent::__construct([]);
        $this->names = $names;
    }
}
