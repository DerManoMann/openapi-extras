<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;

/**
 * Class level annotation to configure all endpoints of a (controller) class.
 *
 * Currently, the following might be shared across all endpoints:
 * - path prefix
 * - (default) responses
 *
 * @Annotation
 */
class Controller extends OA\Attachable
{
    /**
     * A prefix prepended to all paths in this controller.
     */
    public ?string $prefix = null;

    /**
     * The list of shared responses for all endpoints in this controller.
     *
     * @var OA\Response[]|null
     */
    public ?array $responses = null;

    /**
     * @inheritdoc
     */
    public static $_nested = [
        OA\Response::class   => ['responses', 'response'],
        OA\Attachable::class => ['attachables'],
    ];
}
