<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras;

use OpenApi\Generator;

trait JsonContentTrait
{
    /** @var string|class-string */
    public $source = Generator::UNDEFINED;

    public static $_blacklist = ['_context', '_unmerged', '_analysis', 'attachables', 'source'];

    protected function resolveSource(string|object $ref, ?string $type): void
    {
        if (!Generator::isDefault($ref)) {
            $this->source = $ref;
        } elseif ($type !== null) {
            $this->source = $type;
        }
    }
}
