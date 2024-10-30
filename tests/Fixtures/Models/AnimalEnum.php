<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Models;

enum AnimalEnum: string
{
    case Cat = 'cat';
    case Dog = 'dog';
}