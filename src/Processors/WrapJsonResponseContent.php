<?php declare(strict_types=1);

namespace Radebatz\OpenApi\Extras\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Radebatz\OpenApi\Extras\Annotations as OAX;
use Radebatz\OpenApi\Extras\Attributes as OAXT;

class WrapJsonResponseContent
{
    public function __invoke(Analysis $analysis): void
    {
        $responses = $analysis->getAnnotationsOfType([OAX\JsonResponse::class, OAXT\JsonResponse::class]);

        foreach ($responses as $response) {
            $this->wrapContent($response, $analysis);
        }
    }

    protected function wrapContent(OAX\JsonResponse|OAXT\JsonResponse $response, Analysis $analysis): void
    {
        if (Generator::isDefault($response->content) || !is_array($response->content)) {
            return;
        }

        $mediaType = $response->content['application/json'] ?? null;
        if (!$mediaType instanceof OA\MediaType || !$mediaType->schema instanceof OA\Schema) {
            return;
        }

        $original = $mediaType->schema;

        $property = new OA\Property([
            'property' => $response->wrap,
            'ref' => $original->ref,
            '_context' => $original->_context,
        ]);

        $mediaType->schema = new OA\Schema([
            'required' => [$response->wrap],
            'properties' => [$property],
            '_context' => $original->_context,
        ]);

        if ($original instanceof OA\JsonContent) {
            if (method_exists($analysis, 'removeAnnotation')) {
                $analysis->removeAnnotation($original);
            } else {
                $analysis->annotations->offsetUnset($original);
            }
        }
    }
}
