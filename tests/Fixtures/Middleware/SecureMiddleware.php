<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Tests\Fixtures\Middleware;

use OpenApi\Annotations as OA;
use Radebatz\OpenApi\Extras\Attributes\Middleware;
use Radebatz\OpenApi\Extras\ProvidesCustomizersInterface;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_ALL | \Attribute::IS_REPEATABLE)]
class SecureMiddleware extends Middleware implements ProvidesCustomizersInterface
{
    public function __construct()
    {
        parent::__construct(names: ['jwt-auth']);
    }

    public static function customizers(): array
    {
        return [
            OA\Operation::class => [
                fn (OA\Operation $operation) => $operation->security = [['bearerAuth' => []]],
            ],
        ];
    }
}
