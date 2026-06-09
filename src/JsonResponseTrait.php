<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras;

use OpenApi\Generator;

trait JsonResponseTrait
{
    /** @var string|class-string */
    public $source = Generator::UNDEFINED;

    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'source'];

    /**
     * @return array{ref: string|class-string|null, type: string|class-string|null}
     */
    protected function resolveSource(string|object $ref, ?string $type): array
    {
        $resolvedRef = null;
        $resolvedType = null;

        if (!Generator::isDefault($ref)) {
            $resolvedRef = $ref;
            $this->source = $ref;
        }
        if ($type !== null) {
            $resolvedType = $type;
            $this->source = $this->source !== Generator::UNDEFINED ? $this->source : $type;
        }

        return ['ref' => $resolvedRef, 'type' => $resolvedType];
    }
}
