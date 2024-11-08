<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;

/**
 * Class level annotation to configure all endpoints of a (controller) class.
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
     * The list of shared headers for all responses for all endpoints in this controller.
     *
     * @var OA\Header[]|null
     */
    public ?array $headers = null;

    /**
     * @inheritdoc
     */
    public static $_nested = [
        OA\Header::class => ['headers', 'header'],
        OA\Response::class => ['responses', 'response'],
        OA\Attachable::class => ['attachables'],
    ];

    /**
     * @inheritdoc
     */
    public function allowedParents(): ?array
    {
        return [];
    }
}
