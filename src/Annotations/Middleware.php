<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * @Annotation
 */
class Middleware extends OA\Attachable
{
    /**
     * The middleware names.
     *
     * @var array<string|class-string>|null
     */
    public $names = null;

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

    public function __construct(array $names)
    {
        parent::__construct([]);
        $this->names = $names;
    }
}
