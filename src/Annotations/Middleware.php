<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;

/**
 * Middleware name(s) container.
 *
 * @Annotation
 */
class Middleware extends OA\Attachable
{
    /**
     * The middleware names.
     *
     * @var array<string|class-string>|null
     */
    public ?array $names = null;

    /**
     * @inheritdoc
     */
    public static $_required = ['names'];

    /**
     * @inheritdoc
     */
    public function allowedParents(): ?array
    {
        return [OA\Operation::class, Controller::class];
    }

    /**
     * @paramarray<string|class-string>  $names
     */
    public function __construct(array $names)
    {
        parent::__construct([]);
        $this->names = $names;
    }
}
